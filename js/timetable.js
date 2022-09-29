let timetable = {
    fillPopup: async function () {
        const response = await requestHandler.sendRequest('getTimetable')
        const data = response.data
        var html = `<div class="modal-header">
                <div class="modal__header__left">Timetable</div>
                <div class="modal__header__right">
                    <button onclick="timetable.addEntryPopup(${data.id})">Add entry</button>
                    <button onclick="timetable.deleteTimetable(${data.id})">Delete</button>
                </div>
                <i class="fa fa-close fa-2x" aria-hidden="true" id="fa-close-dynamicform"></i>
            </div>`
        if (data && data.tasks) {
            html += `<div class="timetable__content">
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Monday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'mon'))}
                    </div>
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Tuesday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'tue'))}
                    </div>
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Wednesday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'wed'))}
                    </div>
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Thursday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'thu'))}
                    </div>
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Friday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'fri'))}
                    </div>
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Saturday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'sat'))}
                    </div>
                    <div class="timetable__content__row">
                        <div class="timetable__content__head">Sunday</div>
                        ${this.printHtmlDay(data.tasks.filter((entry) => entry.timetableWeekday == 'sun'))}
                    </div>
                </div>`
        }
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    timetablePopup: async function () {
        this.fillPopup()
    },
    createTimetable: async function () {
        await requestHandler.sendRequest('createTimetable')
        this.fillPopup()
    },
    deleteTimetable: async function (id) {
        if (!confirm("Are you sure you want to delete this timetable?")) return
        await requestHandler.sendRequest('deleteTimetable', ['id', id])
        this.fillPopup()
    },
    addEntryPopup: function (id) {
        var container = document.getElementById("dynamic-modal-content")
        var html = `<div class="modal-header">
                <div class="modal__header__left">Add Entry</div>
                <i class="fa fa-close fa-2x" aria-hidden="true" onclick="timetable.loadParentForm()"></i>
            </div>
            <div class="timetable__entry__form">
                <input type="text" name="text" id="text" placeholder="text" />
                <div class="timtetable__entry__form__time">
                    <label htmlFor="start">Start</label>
                    <input type="time" name="start" id="start" value="00:00"/>
                    <label htmlFor="end">End</label>
                    <input type="time" name="end" id="end" value="00:00"/>
                </div>
                <div class="timetable__entry__form__days">
                    <div class="timetable__entry__form__days__column">
                        <input type="checkbox" id="mon" name="mon" /><small>Monday</small>
                        <input type="checkbox" id="tue" name="tue" /><small>Tuesday</small>
                    </div>
                    <div class="timetable__entry__form__days__column">
                        <input type="checkbox" id="wed" name="wed" /><small>Wednesday</small>
                        <input type="checkbox" id="thu" name="thu" /><small>Thursday</small>
                    </div>
                    <div class="timetable__entry__form__days__column">
                        <input type="checkbox" id="fri" name="fri" /><small>Friday</small>
                        <input type="checkbox" id="sat" name="sat" /><small>Saturday</small>
                    </div>
                    <div class="timetable__entry__form__days__column">
                        <input type="checkbox" id="sun" name="sun" /><small>Sunday</small>
                    </div>
                </div>
                <div class="timetable__entry__form__bottom">
                    <div class="timetable__entry__form__intervall">
                        <input type="checkbox" id="monsun" name="monsun" /><small>Mon-Sun</small>
                        <input type="checkbox" id="monfri" name="monfri" /><small>Mon-Fri</small>
                    </div>
                    <div class="timetable__entry__form__submit">
                        <input type="submit" value="Submit" onclick="timetable.addEntry(${id})" />
                    </div>
                </div>
            </div>`
        showDynamicForm(container, html)
        closeDynamicFormListener()
    },
    addEntry: async function (id) {
        var text = document.getElementById("text").value
        var start = document.getElementById("start").value
        var end = document.getElementById("end").value
        if (!(text && start < end)) return printErrorToast("EMPTY_FIELDS")
        await requestHandler.sendRequest(
            'addEntries', ['id', id], ['text', text], ['start', start], ['end', end],
            ['mon', document.getElementById("mon").checked], ['tue', document.getElementById("tue").checked], ['wed', document.getElementById("wed").checked],
            ['thu', document.getElementById("thu").checked], ['fri', document.getElementById("fri").checked], ['sat', document.getElementById("sat").checked],
            ['sun', document.getElementById("sun").checked],
            ['monfri', document.getElementById("monfri").checked], ['monsun', document.getElementById("monsun").checked])
        this.fillPopup()
    },
    loadParentForm: function () {
        var container = document.getElementById("dynamic-modal-content");
        showDynamicForm(container, this.parentHTML)
        closeDynamicFormListener()
    },
    printHtmlDay: function (tasks) {
        var html = ''
        if (tasks) {
            tasks.forEach(task => {
                html += `<div class="timetable__content__block">
                        <div class="timetable__content__task__row">
                            <div class="timetable__content__task__time">${task.timetableTimeStart}-${task.timetableTimeEnd}</div>
                            <div class="timetable__content__task__delete"><i class="fa fa-close" aria-hidden="true" onclick="timetable.deleteEntry(${task.timetableEntryID})"></i></div>
                        </div>
                        <div class="timetable__content__task__text">${task.timetableText}</div>
                    </div>`
            });
        }
        return html
    },
    deleteEntry: async function (id) {
        if (!confirm("Are you sure you want to delete this entry?")) return
        const response = await requestHandler.sendRequest('deleteEntry', ['id', id])
        this.fillPopup(response.data, this.type)
    }
}