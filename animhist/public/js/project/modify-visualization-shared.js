var RESERVED_COLUMN = ["rowid", "createdat", "milestonerep", "geocode", "position", "htmldata"];

function checkInReservedColumn(str) {
	var i = RESERVED_COLUMN.indexOf(str.toLowerCase());
	return i > -1;
}

function checkColumnName(str, columnList, curIndex) {
	if (checkInReservedColumn(str)) return false;
	if (str == "" || !str.match(/^[a-z0-9\-\s]+$/i)) return false;
	
	for (var i = 0; i < columnList.length; i++) {
		if (str.toLowerCase() == columnList[i]["caption"].toLowerCase()) {
			if (curIndex !== undefined) {
				if (i != curIndex) return false;
			} else return false;
		}
	}
	
	return true;
}