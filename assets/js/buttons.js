function confirmRes() {
    //reserve term
    if(document.getElementById('rb1').checked){
        document.getElementById('rez').style.display = "block";
        document.getElementById('choose-student').style.display = "block";
        document.getElementById('rez').value = "R";

        document.getElementById('otkazi').style.display = "none";
        document.getElementById('zatvori').style.display = "none";
        document.getElementById('oslobodi').style.display = "none";
        
    }
}

function cancelRes() {
    //cancel term
    if(document.getElementById('rb2').checked){
        document.getElementById('rez').style.display = "none";

        document.getElementById('otkazi').style.display = "block";
        document.getElementById('otkazi').value = "S";

        document.getElementById('oslobodi').style.display = "none";
        document.getElementById('zatvori').style.display = "none";
        document.getElementById('choose-student').style.display = "none";
    }
}

function freeTerm() {
    //close term
    if(document.getElementById('rb3').checked){
        document.getElementById('rez').style.display = "none";

        document.getElementById('oslobodi').style.display = "block";
        document.getElementById('oslobodi').value = "S";

        document.getElementById('otkazi').style.display = "none";
        document.getElementById('zatvori').style.display = "none";
        document.getElementById('choose-student').style.display = "none";
    }
}

function closeTerm() {
    //close term
    if(document.getElementById('rb4').checked){
        document.getElementById('rez').style.display = "none";
        document.getElementById('otkazi').style.display = "none";

        document.getElementById('zatvori').style.display = "block";
        document.getElementById('zatvori').value = "Z";

        document.getElementById('oslobodi').style.display = "none";
        document.getElementById('choose-student').style.display = "none";
    }
}

function confirmResStudent() {
    //reserve term
    if(document.getElementById('rbS1').checked){
        document.getElementById('rezStudent').style.display = "block";
        document.getElementById('rezStudent').value = "R";

        document.getElementById('otkaziStudent').style.display = "none";
    }
}

function cancelResStudent() {
    //cancel term
    if(document.getElementById('rbS2').checked){
        document.getElementById('rezStudent').style.display = "none";

        document.getElementById('otkaziStudent').style.display = "block";
        document.getElementById('otkaziStudent').value = "S";
    }
}

$(function(){
    $('#myModal').on('show.bs.modal', function(event) {
        var term = $(event.relatedTarget);
        document.querySelector('#term_id').value = term[0].dataset.term;
    });    
});