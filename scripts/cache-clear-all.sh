#!/bin/sh -e

# This scripts clears the cache of all port related data.
# Typically, this data resides at ~freshports/cache/ports/

CONFIG="/usr/local/etc/freshports/config.sh"

if [ ! -f $CONFIG ]
then
        echo "$CONFIG not found by $0..."
        exit 1
fi

. $CONFIG

command="/sbin/zfs list -Hr -o name -d 1 $fp_zfs_caching_parent"

datasets=$(${command} | sed -n -e '2,$p')
for dataset in $datasets
do
  /sbin/zfs rollback $dataset@empty
done
