#!/bin/bash

mysql -u root -e'source sql/clean.sql' || true

mysql -u root -e'source sql/database.sql'

mysql -u oes -p123456 -D oes -e'source sql/structure.sql'

mysql -u oes -p123456 -D oes -e'source sql/data.sql'
