let userHandler = {
    login: async function () {
        const username = document.getElementById('loginUsername').value
        const pw = document.getElementById('loginPassword').value
        if (!username || !pw) return printErrorToast('EMPTY_FIELDS')
        const response = await requestHandler.sendRequest('login', ['username', username], ['pw', pw])
        if (response.ResponseCode != 'OK') return printErrorToast('LOGIN')
        if (response.data) location.href = response.data
        location.href = DIR_SYSTEM
    },
    signup: async function () {
        const username = document.getElementById('signupUsername').value
        const email = document.getElementById('signupEmail').value
        const password = document.getElementById('signupPassword').value
        const passwordRepeat = document.getElementById('signupPasswordRepeat').value
        if (!username || !email || !password || !passwordRepeat) return printErrorToast('EMPTY_FIELDS')
        if (password != passwordRepeat) return printErrorToast('PW_NOT_EQUAL')
        const response = await requestHandler.sendRequest('signup', ['username', username], ['email', email], ['password', password])
        if (response.ResponseCode != 'OK') return printErrorToast(response.ResponseCode)
        location.href = DIR_SYSTEM
        return printSuccessToast('SIGNUP')
    },
    updateShortname: async function () {
        const usernameShort = document.getElementById('updateShortnameUsernameShort').value
        if (!usernameShort) return printErrorToast('EMPTY_FIELDS')
        const response = await requestHandler.sendRequest('updateShortname', ['usernameShort', usernameShort])
        if (response.ResponseCode != 'OK') return printErrorToast(response.ResponseCode)
        return printSuccessToast('UPDATE_SHORTNAME')
    },
    updatePassword: async function () {
        const passwordOld = document.getElementById('updatePasswordPasswordOld').value
        const passwordNew = document.getElementById('updatePasswordPasswordNew').value
        const passwordNewRepeat = document.getElementById('updatePasswordPasswordNewRepeat').value
        if (!passwordOld || !passwordNew || !passwordNewRepeat) return printErrorToast('EMPTY_FIELDS')
        if (passwordNew != passwordNewRepeat) return printErrorToast('PW_NOT_EQUAL')
        const response = await requestHandler.sendRequest('updatePassword', ['passwordOld', passwordOld], ['passwordNew', passwordNew])
        if (response.ResponseCode != 'OK') return printErrorToast(response.ResponseCode)
        return printSuccessToast('UPDATE_PW')
    },
    acceptInvite: async function (token) {
        const response = await requestHandler.sendRequest('acceptInvite', ['token', token])
        if (response.ResponseCode != 'OK') return printErrorToast(response.ResponseCode)
        location.href = `${DIR_SYSTEM}php/details.php?action=groupDetails&id=${response.data}`
        return printSuccessToast('JOINED_GROUP')
    },
    rejectInvite: async function (token) {
        const response = await requestHandler.sendRequest('rejectInvite', ['token', token])
        if (response.ResponseCode != 'OK') return printErrorToast(response.ResponseCode)
        return
    },
    resendVerifymail: async function () {
        await requestHandler.sendRequest('resendVerifymail')
        return printSuccessToast('MAIL_SENT')
    },
    toggleNightmode: async function (checked) {
        await requestHandler.sendRequest('toggleNightmode', ['checked', checked])
    },
    updateMail: async function () {
        const mail = document.getElementById('updateMailMail').value
        if (!mail) return printErrorToast('EMPTY_FIELDS')
        const response = await requestHandler.sendRequest('updateMail', ['mail', mail])
        if (response.ResponseCode != 'OK') return printErrorToast(response.ResponseCode)
        return printSuccessToast('UPDATE_MAIL')
    }
}