#!/bin/bash

test $# -gt 0 && exec <"$1"

SHUFFLEABLE=false
RANK=0.5

echo 'set names utf8;'

sed "s/#.*//g" | sed "/^$/d" | while read LINE
do
	
	if [ "${LINE:0:9}" = "Subject: " ]
	then
		SUBJECT="${LINE:9}"
		echo
		echo "insert ignore into oes_Subject values (null, '$SUBJECT');"
		echo "set @SubjectID = (select ID from oes_Subject where Name = '$SUBJECT');"

	elif [ "${LINE:0:2}" = "Q " ]
	then
		echo
		echo "REPLACE INTO oes_Question values (null, '${LINE:2}', @SubjectID, $SHUFFLEABLE);"
		echo "set @QuestionID = LAST_INSERT_ID();"
	
	elif [ "${LINE:0:1}" = "+" ]
	then
		echo "	REPLACE INTO oes_Choice values (null, @QuestionID, '${LINE:1}', true, true);"
	
	elif [ "${LINE:0:1}" = "-" ]
	then
		echo "	REPLACE INTO oes_Choice values (null, @QuestionID, '${LINE:1}', false, true);"
	fi
done
