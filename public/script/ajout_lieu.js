$(document).ready(function(){
    $("#lieu_form").submit(function(event){
        submitForm();
        return false;
    });
});
let $formLieu = $("#lieu_form");
let $nomLieu = $("#lieu_nom");
let $rueLieu = $("#lieu_rue");
let $ville2 = $("#lieu_ville");
let $token=$('#lieu__token')

function submitForm(){
    let data ={};
    data[$ville2.attr('name')] = $ville2.val();
    data[$nomLieu.attr('name')] = $nomLieu.val();
    data[$rueLieu.attr('name')] = $rueLieu.val();
    data[$token.attr('name')]=$token.val();
    $.ajax({
        url :  $formLieu.attr('action'),
        type: $formLieu.attr('method'),
        data: data
    })
        .done(function (){
            alert("Ajout du lieu réussi");
        })
}