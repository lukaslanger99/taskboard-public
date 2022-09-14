let groupHandler = {
    createGroup: async function () {
        const groupName = document.getElementById('createGroup_groupName')
        if (groupName) {
            const response = await requestHandler.sendRequest('createGroup', ['groupname', groupName])
            const errors = ["GROUPNAME_TAKEN", "MAX_GROUPS", "UNVERIFIED_MAIL"]
            if (errors.includes(response.ResponseCode)) printErrorToast(response.ResponseCode)
        }
    },
    deleteGroup: async function (groupID) {
        if (!confirm("Are you sure you want to delete this group?")) return
        return await requestHandler.sendRequest('deleteGroup', ['groupID', groupID])
    },
    leaveGroup: async function (groupID) {
        if (!confirm("Are you sure you want to leave this group?")) return
        return await requestHandler.sendRequest('leaveGroup', ['groupID', groupID])
    },
    refreshInvites: async function (groupID) {
        const response = await requestHandler.sendRequest('refreshInvites', ['groupID', groupID])
        if (response.ResponseCode != 'OK') return
        closeDynamicForm()
        this.openInvitesPopup(groupID)
    },
    iniviteUser: async function (groupID) {
        const username = document.getElementById('groupInvite_username')
        if (username) {
            return await requestHandler.sendRequest('createGroupInvite', ['groupID', groupID], ['username', username])
        }
    },
    toggleGroupInvites: async function (groupID, status) {
        const response = await requestHandler.sendRequest('toggleGroupInvites', ['groupID', groupID], ['status', status])
        if (response.ResponseCode != 'OK') return
        this.openInvitesPopup(groupID)
    },
    removeUser: async function (groupID, userID, userName) {
        if (!confirm("Are you sure you want to remove " + userName + "?")) return
        const response = await requestHandler.sendRequest('removeUser', ['groupID', groupID], ['userID', userID])
        if (response.ResponseCode != 'OK') return
        closeDynamicForm()
        this.openUsersPopup(groupID)
    },
    openUsersPopup: async function (groupID) {
        const response = await requestHandler.sendRequest('getGroupAccess', ['groupID', groupID])
        const data = response.data
        var html = addHeaderDynamicForm('Group Users')
        if (data.groupAccess) {
            data.groupAccess.forEach(entry => {
                html += `        
                    <div class="display__flex">
                        <p><div>${entry.userName}</div></p>
                        ${(data.groupOwner)
                        ? `<p><i class="fa fa-trash fa-2x" aria-hidden="true" onclick="groupHandler.removeUser(${entry.groupID}, ${entry.userID}, '${entry.userName}')"></i></p>`
                        : ``}
                    </div>
                    <hr class="solid">`
            })
        }
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    openInvitesPopup: async function (groupID) {
        const response = await requestHandler.sendRequest('getGroupIniviteData', ['groupID', groupID])
        if (response.ResponseCode != 'OK') return
        const data = response.data
        var html = `
            ${addHeaderDynamicForm('Group Invites')}
            ${this.getInviteHTML(data)}
            <input type="text" name="name" placeholder="username" id="groupInvite_username">
            <button onclick="groupHandler.iniviteUser(${groupID})">Invite</button>`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    getInviteHTML: function (data) {
        if (!data.groupOwner && data.groupInvites != 'enabled') return ''
        if (!data.groupOwner && data.groupInvites == 'enabled') return `<p>${DIR_SYSTEM}server/request.php?action=joinGroup&t=${data.token}</p>`
        if (data.groupInvites != 'enabled') return `<button class="button" onclick="groupHandler.toggleGroupInvites(${groupID}, 'enabled')">Enable Invites</button>`
        return `<p>${DIR_SYSTEM}server/request.php?action=joinGroup&t=${data.token}</p>
            <p onclick="groupHandler.refreshInvites(${groupID})"><i class="fa fa-refresh" aria-hidden="true"></i></p>
            <button class="button" onclick="groupHandler.toggleGroupInvites(${groupID}, 'disabled')">Disable Invites</button>`
    },
    openSettingsPopup: async function (groupID) {
        const response = await requestHandler.sendRequest('getGroupSettingsData', ['groupID', groupID])
        if (response.ResponseCode != 'OK') return
        const data = response.data
        var html = `
            ${addHeaderDynamicForm('Group Settings')}
            <div class="popop__dropdowns">
                <p>Name:</p>
                <p><input type="text" id="groupName" value="${data.groupName}" ${(data.groupOwner) ? `` : `disabled`}></p>
            </div>
            <div class="popop__dropdowns">
                <p>Priority:</p>
                <p><input type="number" id="groupPriority" min="1" max="1000" value="${data.groupPriority}" ${(data.groupOwner) ? `` : `disabled`}></p>
            </div>
            <div class="popop__dropdowns">
                <p>Number of days till task archived:</p>
                <p><input type="number" id="groupArchiveTime" min="1" max="365" value="${data.groupArchiveTime}" ${(data.groupOwner) ? `` : `disabled`}></p>
            </div>
            <div class="popop__dropdowns">
                <p><input id="groupUnfoldedCheckbox" type="checkbox" ${(data.groupUnfolded) ? 'checked' : ''}></p>
                <p>Unfolded by default on mobile</p>
            </div>
            <div class="popop__dropdowns">
                <p><input id="groupVisibleCheckbox" type="checkbox" ${(data.groupStatus) ? 'checked' : ''} ${(data.groupOwner) ? `` : `disabled`}></p>
                <p>Group is Visible</p>
            </div>
            <button class="button" onclick="groupHandler.updateGroup(${groupID})">Update</button>`
        showDynamicForm(document.getElementById("dynamic-modal-content"), html)
        closeDynamicFormListener()
    },
    updateGroup: async function (groupID) {
        const groupName = document.getElementById('groupName').value
        const groupPriority = document.getElementById('groupPriority').value
        const groupArchiveTime = document.getElementById('groupArchiveTime').value
        const groupUnfolded = document.getElementById('groupUnfoldedCheckbox').checked
        const groupStatus = document.getElementById('groupVisibleCheckbox').checked
        if (!(groupName && groupPriority && groupArchiveTime)) return
        const response = await requestHandler.sendRequest(
            'updateGroup',
            ['groupID', groupID],
            ['groupName', groupName],
            ['groupPriority', groupPriority],
            ['groupArchiveTime', groupArchiveTime],
            ['groupUnfolded', (groupUnfolded) ? 'true' : 'false'],
            ['groupStatus', (groupStatus) ? 'active' : 'hidden']
        )
        if (response.ResponseCode != 'OK') return
        closeDynamicForm()
    }
}