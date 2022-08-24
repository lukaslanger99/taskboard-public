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
    }
}