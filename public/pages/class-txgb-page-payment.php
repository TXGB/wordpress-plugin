<?php

class TXGB_Page_Payment
{
	public function action()
	{
		session_start();

		$product_id = isset($_GET['product']) ? $_GET['product'] : null;

		if (isset($_SESSION['products']) && isset($_SESSION['products'][$product_id])) {
			$product = unserialize($_SESSION['products'][$product_id]);
		} else {
			echo 'Finished.';
			exit;
		}

		return new TXGB_View('booking/payment', compact('product'));
	}
}

