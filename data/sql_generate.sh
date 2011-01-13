#!/bin/bash

test $# -gt 0 && exec <"$1"

BOOLEAN=( false true )
SHUFFLEABLE=true
RANK=0.5
CORRECT=

echo 'set names utf8;'

sed "s/#.*//g" | sed "/^$/d" | while read LINE
do
	LINE="${LINE//\'/\'}"
	if [ "${LINE:0:2}" = "@@" ]
	then
		SUBJECT="`echo ${LINE:2}`"
		echo
		echo "insert ignore into oes_Subject values (null, '$SUBJECT');"
		echo "set @SubjectID = (select ID from oes_Subject where Name = '$SUBJECT');"
		echo "-- Clear old data --"
		echo "delete from oes_Answer where Choice in (select ID from oes_Choice where Question in (select ID from oes_Question where Subject = @SubjectID));"
		echo "delete from oes_TQ where Question in (select ID from oes_Question where Subject = @SubjectID);"
		echo "delete from oes_Test where ID not in (select Test from oes_TQ);"
		echo "delete from oes_Choice where Question in (select ID from oes_Question where Subject = @SubjectID);"
		echo "delete from oes_Question where Subject = @SubjectID;"

	elif [ "${LINE:0:2}" = "??" ]
	then
		QUESTION="`echo ${LINE:2}`"
		echo
		echo "REPLACE INTO oes_Question values (null, '$QUESTION', @SubjectID, $SHUFFLEABLE, $RANK);"
		echo "set @QuestionID = LAST_INSERT_ID();"
		CORRECT=true

	elif [ "${LINE:0:2}" = "**" ]
	then
		CHOICE="`echo ${LINE:2}`"
		echo "	REPLACE INTO oes_Choice values (null, @QuestionID, '$CHOICE', $CORRECT, ${BOOLEAN[RANDOM%2]});"
		CORRECT=false
	else
		if [ "$CORRECT" = true ]
		then
			QUESTION="$QUESTION\n$LINE"
			echo "UPDATE oes_Question set Text = '$QUESTION' where ID = @QuestionID;"
		else
			CHOICE="$CHOICE\n$LINE"
			echo "UPDATE oes_Choice set Text = '$CHOICE' where ID = LAST_INSERT_ID();"
		fi
	fi
done
