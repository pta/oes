#!/bin/bash

for F in *.txt
do
	echo $F
	./sql_generate.sh $F | mysql -uroot -proot oes
done
