#!/bin/bash

cd "$(dirname "$0")/.."

ansible-playbook -i .ansistrano/hosts-production .ansistrano/deploy.yml
