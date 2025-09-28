#!/bin/bash

cd "$(dirname "$0")/.."

ansible-playbook -i .ansistrano/hosts-preproduction .ansistrano/deploy.yml
