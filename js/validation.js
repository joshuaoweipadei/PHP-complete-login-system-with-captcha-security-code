function validate_registration(){
    $("#firstname_err").html('')
    $("#lastname_err").html('')
    $("#email_err").html('')
    $("#password_err").html('')
    $("#passwordAgain_err").html('')

    var firstname = $("#firstname").val();
    var lastname = $("#lastname").val();
    var email = $("#email").val();
    var password = $("#password").val();
    var passwordAgain = $("#passwordAgain").val();
    var captcha = $("#captcha_code").val();
    var nameRegex = /^[a-zA-Z]+$/;
    var phoneRegex = /^[0][7-9]{1}[0-1]{1}[2-9]{1}[0-9]{7}$/;
    var emailRegex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    var valid = true;

    if(firstname == ''){
        valid = false;
        $("#firstname").focus();
        $("#firstname_err").html('First name is required')
        return valid
    } else{
        if(!firstname.match(nameRegex)){
            valid = false;
            $("#firstname").focus();
            $("#firstname_err").html('Invalid firstname character!')
            return valid;
        }
    }

    if(lastname == ''){
        valid = false;
        $("#lastname").focus();
        $("#lastname_err").html('Last name is required')
        return valid
    } else{
        if(!lastname.match(nameRegex)){
            valid = false;
            $("#lastname").focus();
            $("#lastname_err").html('Invalid lastname character!')
            return valid;
        }
    }

    if(email == ''){
        valid = false;
        $("#email").focus();
        $("#email_err").html('Email address is required')
        return valid
    } else{
        if(!emailRegex.test(email)){
            valid = false;
            $("#email").focus();
            $("#email_err").html('Invalid email address!')
            return valid;
        }
    }

    if(password == ''){
        valid = false;
        $("#password").focus();
        $("#password_err").html('Password is required')
        return valid
    } else{
        if(password.length < 4){
            valid = false;
            $("#password").focus();
            $("#password_err").html('Password must be 4 or more characters!')
            return valid
        }
    }

    if(passwordAgain == ''){
        valid = false;
        $("#passwordAgain").focus();
        $("#passwordAgain_err").html('Password again is required')
        return valid
    } else{
        if(passwordAgain != password){
            valid = false;
            $("#passwordAgain").focus();
            $("#passwordAgain_err").html('Passwords do not match!')
            return valid
        }
    }

    if(captcha == ""){
        valid = false;
        $("#captcha_code_err").html("(required)");
        return valid;
    }

    return valid;
}