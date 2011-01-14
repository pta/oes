#!/bin/bash

mysql -uroot -proot -e'source sql/clean.sql' || true

mysql -uroot -proot -e'source sql/database.sql'

mysql -uoes -p123456 -D oes -e'source sql/structure.sql'

mysql -uoes -p123456 -D oes -e'source sql/data.sql'
