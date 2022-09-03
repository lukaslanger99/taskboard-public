let groupHandler = {
    createGroup: async function () {
        const groupName = document.getElementById('createGroup_groupName')
        if (groupName) {
            const responseCode = await requestHandler.sendRequest('createGroup', ['groupname', groupName])
            const errors = ["groupnametaken","maxgroups","unverifiedmail"]
            if (errors.includes(responseCode)) printErrorToast(responseCode)
        }
    },
    deleteGroup: async function (groupID) {
        if (!confirm("Are you sure you want to delete this group?")) return
        return await requestHandler.sendRequest('createGroup', ['groupID', groupID])
    },
    createGroupInvite: async function (groupID) {
        const username = document.getElementById('groupInvite_username')
        if (username) {
            return await requestHandler.sendRequest('createGroupInvite', ['groupID', groupID], ['username', username])
        }
    },
    toggleGroupInvites: async function (groupID, status) {
        return await requestHandler.sendRequest('toggleGroupInvites', ['groupID', groupID], ['status', status])
    },
    toggleGroupStatus: async function (groupID, status) {
        return await requestHandler.sendRequest('toggleGroupStatus', ['groupID', groupID], ['status', status])
    }
}