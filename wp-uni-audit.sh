#! /bin/bash
###
# wp-uni-audit.sh
#
# Checks if a CUL Wordpress user's user_login is a UNI in LDAP
# and if that UNI is Staff and/or Retired.
# Requires wp-cli & must be run from within a Wordpress root dir.
#
###

# If it's a multisite, uncomment MSURL and give it the full URL.
#MSURL="https://example.com"

## Set Privileged LDAP username and location of password file.
SERVICE_ACCOUNT_USERNAME="YOUR_USERNAME"
SERVICE_SECRET="/path/to/ldap-secret.txt"

## No need to edit below here.
IFS=$'\n'

for user in $(wp user list ${MSURL:+"--url=$MSURL"} ${MSURL:+"--network"} --format=csv --fields=ID,user_login,display_name,user_email,user_registered)
  do
    id=$(cut -d',' -f1 <<< "$user")
    uni=$(cut -d',' -f2 <<< "$user")
    dname=$(cut -d',' -f3 <<< "$user")
    email=$(cut -d',' -f4 <<< "$user")
    registered=$(cut -d',' -f5 <<< "$user")

    ldo=$(ldapsearch -y "$SERVICE_SECRET" -D "uni=$SERVICE_ACCOUNT_USERNAME,ou=People,o=Columbia University,c=US" -x -H ldaps://ldap.columbia.edu:636 -LLL -b "ou=People,o=Columbia University, c=us" "(uid=$uni)")

    if grep -q "affiliation: SLbasicStaff" <<< $ldo ; then
      staffout="STAFF"
    else
      staffout=""
    fi
    if grep -q "affiliation: CUretiree" <<< $ldo ; then
      retireeout="RETIREE"
    else
      retireeout=""
    fi
    if [[ -z "$ldo" ]] ; then
        ldapout="NOT_IN_LDAP"
    else
        ldapout=""
    fi
    echo "$id,$uni,$dname,$email,$registered,$staffout,$retireeout,$ldapout"
done
