for F in logic.svg concept.svg
do
	inkscape -f "$F" -l "$F.svg" -T --vacuum-defs
	inkscape -f "$F" -e "${F%%.svg*}.png" -w 1000
	#inkscape -f "$F" -e "${F%%.svg*}.print.png" -w 13000
done
