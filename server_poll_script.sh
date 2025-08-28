#!/bin/bash

FILE="/home/ubuntu/fabric/logs/latest.log"
SERVER_BOOTED_REGEX="\[Server thread/INFO\]: Done (.*)! For help, type \"help\""
CHUNKY_FINISHED_REGEX="\[Server thread/INFO\]: \[Chunky\] Task finished for minecraft:overworld. Processed:"

if grep -q "$SERVER_BOOTED_REGEX" "$FILE"; then
  echo "Server has booted; triggering Chunky"
fi

if grep -q "$CHUNKY_FINISHED_REGEX" "$FILE"; then
  echo "Chunky has finished."
fi
