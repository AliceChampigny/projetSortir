$(document).ready(function(){
    $("#lieu_form").submit(function(event){
        submitFormLieu();
        return false;
    });
});

function submitFormLieu(){
    let $formLieu = $("#lieu_form");
    let $nomLieu = $("#lieu_nom");
    let $rueLieu = $("#lieu_rue");
    let $ville2 = $("#lieu_ville");
    let $longitude=$("#lieu_longitude")
    let $latitude=$("#lieu_latitude")
    let $token=$('#lieu__token')
    let data ={};
    data[$ville2.attr('name')] = $ville2.val();
    data[$nomLieu.attr('name')] = $nomLieu.val();
    data[$rueLieu.attr('name')] = $rueLieu.val();
    data[$longitude.attr('name')] = $longitude.val();
    data[$latitude.attr('name')] = $latitude.val();
    data[$token.attr('name')]=$token.val();
    $.ajax({
        url :  $formLieu.attr('action'),
        type: $formLieu.attr('method'),
        data: data
    })
        .done(function (){
            let $modal=$('#myModal');

            $modal.removeClass("show");
            console.log($modal);
        })
}


$(document).ready(function(){
    $("#ville_form").submit(function(event){
        submitFormVille();
        return false;
    });
});

function submitFormVille(){
    let $formVille=$('#ville_form');
    let $nomVille=$('#ville_nom');
    let $cpVille=$('#ville_codePostal');
    let $tokenVille=$('#ville__token');
    let $select =$('#listeVille').val().split("-");
    $nomVille.val($select[0]);
    $cpVille.val($select[1]);
    let dataVille ={};

    console.log($select);
    dataVille[$nomVille.attr('name')] = $select[0];
    dataVille[$cpVille.attr('name')] = $select[1];
    dataVille[$tokenVille.attr('name')]=$tokenVille.val();
    $.ajax({
        url :  $formVille.attr('action'),
        type: $formVille.attr('method'),
        data: dataVille
    })
        .done(function (){
            let $modal=$('#myModal2');
            $modal.removeClass("show");
            console.log($modal);
        })
}