// SUCCESS
var successMap = new Map();

function fillSuccessMap() {
    var mapData = {
        "APPOINTMENT_CREATED": "Successfully created appointment !",
        "COMMENT_CREATED": "Successfully created comment !",
        "COMMENT_UPDATED": "Successfully updated comment !",
        "DELETE_COMMENT": "Successfully deleted comment !",
        "DELETE_GROUP": "Successfully deleted group !",
        "DELETE_MESSAGE": "Successfully deleted message !",
        "DELETE_TASK": "Successfully deleted task !",
        "RESOLVED_TASK": "Successfully resolved task !",
        "GROUP_CREATED": "Successfully created group !",
        "INVITED": "Successfully send invite !",
        "JOINED_GROUP": "Successfully joined group !",
        "LEAVE_GROUP": "Successfully left group !",
        "LOGIN": "Successfully logged in !",
        "MAIL_SENT": "Successfully sent verify mail !",
        "MOTD_CREATED": "Successfully created Motd !",
        "PW_RESET": "Password successfully changed !",
        "PW_RESET_MAIL_SENT": "Mail to reset your password successfully sent !",
        "QUEUE_ADDED": "Successfully added to queue !",
        "REMOVED_USER": "Successfully removed user !",
        "SIGNUP": "Successfully signed up !",
        "TASK_ASSIGNED": "Successfully assigned task !",
        "TASK_OPEN": "Successfully set task back to open !",
        "TASK_CREATED": "Successfully created task !",
        "UPDATED_GROUP": "Successfully updated group !",
        "UPDATED_MESSAGE": "Successfully updated message !",
        "TASK_UPDATED": "Successfully updated task !",
        "UPDATE_MAIL": "Successfully updated mail !",
        "UPDATE_PW": "Successfully updated password !",
        "UPDATE_SHORTNAME": "Successfully updated shortname !",
        "VERIFY": "Successfully verified !"
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
        "EMPTY_FIELDS": "Empty Fields !",
        "GROUPNAME_TAKEN": "Groupname already taken !",
        "HIGH_NUMBER": "Failed, number was to high !",
        "INVALID_CHARS": "Invalid characters !",
        "INVALID_INPUT": "Invalid Input !",
        "INVALID_TOKEN": "Invalid token !",
        "INVALID_URL": "Invalid Url !",
        "LOGIN": "Wrong username or password !",
        "MAIL_TAKEN": "Mail already taken !",
        "MAX_GROUP_USERS": "Max. amount of groupusers !",
        "NO_GROUPS": "No groups, create a group first !",
        "NO_USER_FOUND": "No user found !",
        "PW_NOT_EQUAL": "Passwords are not equal !",
        "UNRESOVED_SUBTASKS": "Unresolved subtasks !",
        "UNVERIFIED_MAIL": "Verify your mail before creating new groups !",
        "USERNAME_TAKEN": "Username already taken !",
        "WRONG_MAIL": "Wrong mail !",
        "WRONG_PW": "Wrong password !"
    };

    for (const [key, value] of Object.entries(mapData)) {
        errorMap.set(key, value);
    };
}

function printErrorToast(key) {
    fillErrorMap();
    tata.error(errorMap.get(key), '', {
        position: 'bl',
        duration: 2000
    });
}

// WARNING
var warningMap = new Map();

function fillWarningMap() {
    var mapData = {
        "alreadyjoined": "Already joined this group !"
    };

    for (const [key, value] of Object.entries(mapData)) {
        warningMap.set(key, value);
    };
}

function printWarningToast(key) {
    fillWarningMap();
    tata.warn(warningMap.get(key), '', {
        position: 'bl',
        duration: 2000
    });
}