// SUCCESS
var successMap = new Map();

function fillSuccessMap() {
    var mapData = {
        "appointmentcreated": "Successfully created appointment !",
        "commentcreated": "Successfully created comment !",
        "commentupdated": "Successfully updated comment !",
        "deletecomment": "Successfully deleted comment !",
        "deletegroup": "Successfully deleted group !",
        "deletemessage": "Successfully deleted message !",
        "deletetask": "Successfully deleted task !",
        "deletesubtask": "Successfully deleted subtask !",
        "finishedtask": "Successfully finished task !",
        "finishedsubtask": "Successfully finished subtask !",
        "groupcreated": "Successfully created group !",
        "invited": "Successfully send invite !",
        "leavegroup": "Successfully left group !",
        "login": "Successfully logged in !",
        "mailsend": "Successfully send verify mail !",
        "mailtaken": "Mail already taken !",
        "motdcreated": "Successfully created Motd !",
        "pwreset": "Password successfully changed !",
        "pwresetmailsend": "Mail to reset your password successfully send !",
        "queueadded": "Successfully added to queue !",
        "removeduser": "Successfully removed user !",
        "rtcreated": "Successfully created repeating task !",
        "signup": "Successfully signed up !",
        "subtaskcreated": "Successfully created subtask !",
        "taskassigned": "Successfully assigned task !",
        "taskcreated": "Successfully created task !",
        "updatedgroup": "Successfully updated group !",
        "updatedmessage": "Successfully updated message !",
        "updatedtask": "Successfully updated task !",
        "updatemail": "Successfully updated mail !",
        "updatepw": "Successfully updated password !",
        "updateshortname": "Successfully updated shortname !",
        "verify": "Successfully verified !"
    };

    for (const [key, value] of Object.entries(mapData)) {
        successMap.set(key, value);
    };
}

function printSuccessToast(key) {
    fillSuccessMap();
    tata.success(successMap.get(key), '', {
        position: 'bl',
        duration: 2000
    });
}

// ERROR
var errorMap = new Map();

function fillErrorMap() {
    var mapData = {
        "emptyfield": "Empty Fields !",
        "invalidInput": "Invalid Input !",
        "login": "Wrong username or password !",
        "mailtaken": "Mail already taken !",
        "nouserfound": "No user found !",
        "pwnotequal": "Passwords are not equal !",
        "unfinishedsubtasks": "Unfinished subtasks !",
        "unverifiedmail": "Verify your mail before creating new groups !",
        "wrongmail": "Wrong mail !"
    };

    for (const [key, value] of Object.entries(mapData)) {
        errorMap.set(key, value);
    };
}

function printErrorToast(key) {
    fillErrorMap();
    tata.success(errorMap.get(key), '', {
        position: 'bl',
        duration: 2000
    });
}

// WARNING
var warningMap = new Map();

function fillWarningMap() {
    var mapData = {

    };

    for (const [key, value] of Object.entries(mapData)) {
        warningMap.set(key, value);
    };
}

function printWarningToast(key) {
    fillWarningMap();
    tata.success(warningMap.get(key), '', {
        position: 'bl',
        duration: 2000
    });
}