let groupHandler = {
    createGroup: async function () {
        const groupName = document.getElementById('createGroup_groupName')
        if (groupName) {
            var url = `${DIR_SYSTEM}server/request.php?action=createGroup`
            var formData = new FormData()
            formData.append('groupname', groupName)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            const responseCode = await response.json()
            const errors = ["groupnametaken","maxgroups","unverifiedmail"]
            if (errors.includes(responseCode)) printErrorToast(responseCode)
        }
    },
    deleteGroup: async function (groupID) {
        if (!confirm("Are you sure you want to delete this group?")) return
        var url = `${DIR_SYSTEM}server/request.php?action=createGroup`
        var formData = new FormData()
        formData.append('groupID', groupID)
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        await response.json()
    },
    createGroupInvite: async function (groupID) {
        const username = document.getElementById('groupInvite_username')
        if (username) {
            var url = `${DIR_SYSTEM}server/request.php?action=createGroupInvite`
            var formData = new FormData()
            formData.append('groupID', groupID)
            formData.append('username', username)
            const response = await fetch(
                url, { method: 'POST', body: formData }
            )
            await response.json()
        }
    },
    toggleGroupInvites: async function (groupID, state) {
        var url = `${DIR_SYSTEM}server/request.php?action=toggleGroupInvites`
        var formData = new FormData()
        formData.append('groupID', groupID)
        formData.append('state', state) //enabled, disabled
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        await response.json()
    },
    toggleGroupState: async function (groupID, state) {
        var url = `${DIR_SYSTEM}server/request.php?action=toggleGroupState`
        var formData = new FormData()
        formData.append('groupID', groupID)
        formData.append('state', state) //active, hidden
        const response = await fetch(
            url, { method: 'POST', body: formData }
        )
        await response.json()
    }
}