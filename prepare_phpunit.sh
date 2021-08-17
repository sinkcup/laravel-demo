#!/usr/bin/env bash
set -e
env_file='.env'
env_testing_file='.env.testing'

if [ -f $env_testing_file ]; then
  file=$env_testing_file
else
  file=$env_file
fi

# check db_host
db_host_whitelists=("mysql" "localhost" "127.0.0.1" "0.0.0.0")
db_host_name=DB_HOST

db_host=$(< ${file} grep -E "^${db_host_name}" | awk -F '=' '{print $2}')

if ! printf '%s\n' "${db_host_whitelists[@]}" | grep -q -P "^${db_host}$"; then
  read  -r -p "${db_host_name} [${db_host}] is not in whitelists, continue? (yes/no) [no]:" anwser
  if [ "$anwser" != 'yes' ]; then
    exit
  fi
fi
