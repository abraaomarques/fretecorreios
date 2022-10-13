require(
    [
        'jquery',
        'AbraaoMarques_Correios/js/lib/jquery.maskedinput'
    ],
    function($){
        $("#zipcode").mask("99999-999");
        $("#zipcode").keyup(function (e){
            let zipcode = $("#zipcode").val();
            let weight = $("#weight").val();
            let width = $("#width").val();
            let height = $("#height").val();
            let length = $("#length").val();
            let number = zipcode.replace(/[^\d]/g,"");
            if (number.length == 8) {
                $('.quotation small').show();
                $.ajax({
                    url: '/abraaomarques_correios/search/quotation',
                    type: 'POST',
                    data: {'zipcode': zipcode, 'weight': weight, 'width': width, 'height': height, 'length':length},
                    success: function(response){
                        $('.quotation small').hide();
                        $(".display-quotation").html(response);
                    },
                    error: function(){

                    }
                })
            }
        });
    }
)
