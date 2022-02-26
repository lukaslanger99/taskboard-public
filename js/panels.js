let panels = {
    getQueueTasks: async function () {
        const response = await fetch(
            `${DIR_SYSTEM}server/request.php?action=getQueueTasks`
        )
        return await response.json()
    },
    printQueueTasks: async function (queueTasks = '') {
        if (queueTasks == '') queueTasks = await this.getQueueTasks()
        var html = '', toggle = false, title = ''
        if (queueTasks) {
            queueTasks.forEach(task => {
                html += `<div class="${(toggle) ? `panel-item-content-item` : `panel-item-content-item__secondary`}">
                    <div class="panel-item-message-title">${task.messageTitle}</div>
                        <div class="panel-item-check-button" onclick="panels.deleteQueueTask(${task.messageID})">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </div>
                    </div>`
                toggle = !toggle
            })
            document.getElementById('queuePanelContentArea').innerHTML = html
            if (queueTasks.length == 1) title = `Queue (1 Task)`
            else if (queueTasks.length > 1) title = `Queue (${queueTasks.length} Tasks)`
        } else {
            title = `Queue`
            document.getElementById('queuePanelContentArea').innerHTML = ''
        }
        document.getElementById('queuePanelTitle').innerHTML = title
    },
    deleteQueueTask: async function (id) {
        const response = await fetch(
            `${DIR_SYSTEM}server/request.php?action=deleteQueueTask&id=${id}`
        )
        this.printQueueTasks(await response.json())
    },
    addQueueTask: async function () {
        var url = `${DIR_SYSTEM}server/request.php?action=addQueueTask`
        var formData = new FormData()
        formData.append('text', document.getElementById("queueItem").value)
        formData.append('check', document.getElementById("queueHighprio").checked)
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        document.getElementById("queueItem").value = ''
        document.getElementById("queueHighprio").checked = false
        this.printQueueTasks(await response.json())
    }
}