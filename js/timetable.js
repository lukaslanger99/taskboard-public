let timetable = {
    fillPopup: function (data) {
        (data) ? console.log(data) : console.log("no table")
        // dann mit return füllen, title mit weeknumber, tasks wenn vorhanden.
    },
    timetablePopup: function (type) {
        fetch(
            `${DIR_SYSTEM}server/request.php?action=getTimetable&type=${type}`
        ).then((response) => response.json())
            .then((data) => {
                this.fillPopup(data)
            })
    }
}