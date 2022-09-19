const NAME = 0, VALUE = 1

let requestHandler = {
    sendRequest: async function (actionName, ...dataList) {
        var url = `${DIR_SYSTEM}server/request.php?action=${actionName}`
        if (dataList) {
            var formData = new FormData()
            dataList.forEach(e => {
                formData.append(e[0], e[1])
            })
            const response = await fetch(
                url, { method: 'POST', body: formData }
                )
            return await response.json()
        }
        const response = await fetch(url)
        return await response.json()
    }
}