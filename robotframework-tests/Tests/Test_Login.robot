*** Settings ***
Documentation    Test Login Functionality
Resource         ../Resources/CommonFunctionality.robot
Resource         ../Resources/LoginDefinedKeywords.robot

Test Setup       CommonFunctionality.Start Testcase
Test Teardown    CommonFunctionality.Finish Testcase

*** Variables ***

*** Test Cases ***
Test Login Functionality
    [Documentation]    This test case verifies the login functionality
    [Tags]             Functional

    LoginDefinedKeywords.Verify Login Functionality

Test Appointment Functionality
