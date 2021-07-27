<div class="mr-pay-merchant-settings">
    <h2>MR PAY LATER</h2>
    <?php echo '<a class="button button-large" href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mrpaylater' ) . '">' . __( 'Configure in Woocommerce >>', 'mrpay' ) . '</a>' ?>
    <hr />
    <h4>Contact <a href="mailto:admin@mrpaylater">admin@mrpaylater</a> to request merchant code.</h4>
    <form action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th>Merchant code</th>
                    <td>
                        <input
                            type="text"
                            name="mr_pay_merchant_code"
                            id="mr_pay_merchant_code"
                            class="regular-text"
                            value="<?php echo get_option( 'mr_pay_merchant_code' ) ?>"
                        />
                    </td>
                </tr>
                <tr>
                    <th class="spinner-holder"><div class="spinner"></div></th>
                    <td>
                        <?php submit_button( 'Save merchant code', 'large', 'save_merchant_code' )?>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="action" value="mr_pay_save_merchant_code" />
        <?php wp_nonce_field( 'mr_pay_save_merchant_code' );?>
    </form>
</div>
<div class="mr-pay-transactions">
<h2>Transactions</h2>
<hr>
    <?php
        $transactions->_show( $transactions );
    ?>
</div>