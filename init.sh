#!/bin/bash

mysql -u root -e'source sql/clean.sql' || true

mysql -u root -e'source sql/database.sql'

mysql -u pta_home -p123456 -D pta_home -e'source sql/structure.sql'

mysql -u pta_home -p123456 -D pta_home -e'source sql/data.sql'
