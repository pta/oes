#!/bin/bash

for F in *.dia
do
	SVG="${F%%.*}.svg"
	PNG="${F%%.*}.png"

	if [ "$SVG" -nt "$PNG" ]
	then
		inkscape -f "$SVG" -l "$SVG.svg" -T --vacuum-defs
		inkscape -f "$SVG" -e "$PNG" -w 1000
		#inkscape -f "$SVG" -e "hidpi_$PNG" -w 13000
	fi
done
