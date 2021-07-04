function validateLoginForm(){
    let status = true;
    const username = document.querySelector('#input_username').value;
    const password = document.querySelector('#input_password').value;
    document.querySelector('#error-message').classList.add('d-none');
    document.querySelector('#error-message').innerHTML = '';
    if(!username.match(/^([A-Za-z][A-Za-z0-9._-]{3,})$/)){
        document.querySelector('#error-message').innerHTML += 'Korisničko ime nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }
    if(!password.match(/^[A-Za-z0-9#!@\?~]{7,}$/)){
        document.querySelector('#error-message').innerHTML += 'Lozinka nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }

    return status;
}

function validateRegisterForm(){
    let status = true;
    const name = document.querySelector('#input_name').value;
    const surname = document.querySelector('#input_surename').value;
    const email = document.querySelector('#input_email').value;
    const username = document.querySelector('#input_username').value;
    const password = document.querySelector('#input_password_1').value;
    const password2 = document.querySelector('#input_password_2').value;
    document.querySelector('#error-message').classList.add('d-none');
    document.querySelector('#error-message').innerHTML = '';
    if(!name.match(/^([A-Za-z0-9]{3,})$/)){
        document.querySelector('#error-message').innerHTML += 'Ime nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }
    if(!surname.match(/^[A-Za-z0-9- ]{3,}$/)){
        document.querySelector('#error-message').innerHTML += 'Prezime nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }
    if(!email.match(/^[a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/)){
        document.querySelector('#error-message').innerHTML += 'E-mail nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }
    if(!username.match(/^([A-Za-z][A-Za-z0-9._-]{4,})$/)){
        document.querySelector('#error-message').innerHTML += 'Korisničko ime nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }
    if(!password.match(/^[A-Za-z0-9]{7,}$/)){
        document.querySelector('#error-message').innerHTML += 'Lozinka nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }
    if(!password2.match(/^[A-Za-z0-9]{7,}$/)){
        document.querySelector('#error-message').innerHTML += 'Ponovljena lozinka nije ispravnog formata! <br>';
        document.querySelector('#error-message').classList.remove('d-none');
        status = false;
    }

    return status;
}