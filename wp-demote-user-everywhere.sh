#! /bin/bash
###
# wp-demote-user-everywhere.sh
#
# Reads usernames from list (one per line) then checks each site 
# in the network for their role. If role is greater than 
# subscriber, demote them to subscriber.
# Requires wp-cli & must be run from within a Wordpress root dir.
#
###

NAMESFILE="demote-list.txt"
MSURL="https://blogs.cul.columbia.edu"

while read NAME
do
    if [[ -n "$NAME" ]] ; then
	echo "$NAME:"

	for url in $(wp site list --url="$MSURL" --format=csv --fields=url | tail -n +2)
	do
	  
	  wpo=$(wp --url=$url user get --field=roles "$NAME")
	  if [[ -n "$wpo" ]] ; then
	    if [[ "$wpo" != "subscriber" ]] ; then
	      # found on site but they're not a subscriber. demote to subscriber."
              wp --url="$url" user set-role "$NAME" subscriber
	    #else
	      #then they must be a subscriber
	      #echo "$url + $wpo"
	    fi
	  fi
	done
	echo
    fi
done < $NAMESFILE
