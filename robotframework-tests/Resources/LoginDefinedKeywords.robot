*** Settings ***
Library    SeleniumLibrary

*** Keywords ***
Verify Login Functionality
    Input Text             name:username        robottest
    Input Text             name:password        pwd123#
    Press Keys             name:login-submit    [Return]
    Page Should Contain    TaskBoard