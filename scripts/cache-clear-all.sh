#!/bin/sh -e

# This scripts clears the cache of all port related data.
# Typically, this data resides at ~freshports/cache/ports/

cd ~freshports/cache/ports

rm -rf *

echo deleted.
