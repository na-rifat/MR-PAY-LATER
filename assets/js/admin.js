(function ($) {
    $(`.mr-pay-merchant-settings form`).on(`submit`, function (e) {
        e.preventDefault();
        
        let self = $(this);
        if(self.find(`#mr_pay_merchant_code`).val().length == 0){
            alert(`Please fill the merchant code field.`);
            return;
        }


        let data = self.serialize();

        self.find(`.spinner`).addClass(`is-active`);

        $.ajax({
            type: "POST",
            url: mr_pay.ajax_url,
            data: data,
            dataType: "json",
            success: function (response) {
                self.find(".spinner").removeClass(`is-active`);
                if (response.success) {
                    self.find(`.spinner-holder`).html(response.data.message);

                    setTimeout(() => {
                        self.find(`.spinner-holder`).html(
                            `<div class="spinner"></div>`
                        );
                    }, 1500);
                } else {
                    self.find(`input[type="text"]`).val(``);
                    alert(response.data.message);
                }
            },
            error: (response) => {
                self.find(".spinner").removeClass(`is-active`);
                alert(`Someting wrong happen with the server or connection!`);
            },
        });
    });

    $(`.mr_pay_save_merchant_code`).css({
        display: `flex`,
        justifyContent: `center`,
        alignItems: `center`,
    });
})(jQuery);
