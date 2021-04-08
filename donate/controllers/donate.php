<?php
//error_reporting(E_ALL);
class Donate extends MX_Controller {
    private $fields = array();
    function __construct()
    {
        // Call the constructor of MX_Controller
        parent::__construct();
        // Make sure that we are logged in
        $this->user->userArea();

        $this->load->config('donate');
    }

    public function index()
    {
        requirePermission("view");

        $this->template->setTitle(lang("donate_title", "donate"));

        $donate_zarinpal = $this->config->item('donate_zarinpal');
        $donate_paygol = $this->config->item('donate_paygol');

        $user_id = $this->user->getId();

        $data = array(
            "donate_zarinpal" => $donate_zarinpal,
            "donate_paygol" => $donate_paygol,
            "user_id" => $user_id,
            "server_name" => $this->config->item('server_name'),
            "currency" => $this->config->item('donation_currency'),
            "currency_sign" => $this->config->item('donation_currency_sign'),
            "multiplier" => $this->config->item('donation_multiplier'),
            "multiplier_paygol" => $this->config->item('donation_multiplier_paygol'),
            "url" => pageURL
        );

        $output = $this->template->loadPage("donate.tpl", $data);

        $this->template->box("<span style='cursor:pointer;' onClick='window.location=\"" . $this->template->page_url . "ucp\"'>" . lang("ucp") . "</span> &rarr; " . lang("donate_panel", "donate"), $output, true, "modules/donate/css/donate.css", "modules/donate/js/donate.js");
    }

    public function success()
    {
        $this->user->getUserData();

        $page = $this->template->loadPage("success.tpl", array('url' => $this->template->page_url));

        $this->template->box(lang("donate_thanks", "donate"), $page, true);
    }

    public function zarinpal()
    {
        $this->session->unset_userdata('Amount');
        $donate_zarinpal = $this->config->item('donate_zarinpal');
        $Amount = $this->input->post("amount");
        $Description = $this->input->post("item_name");
        $this->session->set_userdata('Amount', $Amount);

        $param_request = array(
            'merchant_id' => $donate_zarinpal["MerchantID"],
            'amount' => $Amount,
            'description' => $Description,
            'callback_url' => $donate_zarinpal["postback_url"]
        );
        $jsonData = json_encode($param_request);

        $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ));

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $result = json_decode($result, true, JSON_PRETTY_PRINT);
        curl_close($ch);

        if ($result['data']['code'] == 100) {
            echo' <html><body>
                    <script type="text/javascript" src="https://cdn.zarinpal.com/zarinak/v1/checkout.js"></script>
                    <script type="text/javascript">
                    window.onload = function () {
                    Zarinak.setAuthority("' . $result['data']['authority'] . '");
                    Zarinak.showQR();
                    Zarinak.open();
                     };
                    </script></body></html>';
        } else {
            echo'ERR: ' . $result['errors']['code'];
            exit;
        }
    }
    public function zarinpalreturnback()
    {
        $donate_zarinpal = $this->config->item('donate_zarinpal');
        $MerchantID = $donate_zarinpal["MerchantID"];
        $Amount = $this->session->userdata('Amount');
        $Authority = $this->input->get("Authority");
        $status = $this->input->get("Status");
        $this->session->unset_userdata('Amount');

        if ($status == 'OK') {
            // URL also Can be https://ir.zarinpal.com/pg/services/WebGate/wsdl
            $param_verify = array("merchant_id" => $MerchantID, "authority" => $Authority, "amount" => $Amount);
            $jsonData = json_encode($param_verify);
            $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ));

            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            $result = json_decode($result, true);

            if ($result['data']['code'] == 100) {
                // echo 'Transation success. RefID:'. $result->RefID;
                $this->fields['message_id'] = $result['data']['ref_id'];
                $this->fields['custom'] = $user_id = $this->user->getId();
                $this->fields['points'] = $this->getDpAmount($Amount);
                $this->fields['timestamp'] = time();
                $this->fields['converted_price'] = $Amount;
                $this->fields['currency'] = $this->config->item('donation_currency_sign');
                $this->fields['price'] = $Amount;
                $this->fields['country'] = 'SE';
                $this->db->query("UPDATE `account_data` SET `dp` = `dp` + ? WHERE `id` = ?", array($this->fields['points'], $this->fields['custom']));
                $this->updateMonthlyIncome($Amount);
                $this->db->insert("paygol_logs", $this->fields);
                redirect($this->template->page_url."ucp");
                exit;
                //die('success');
            } else {
                echo 'Transation failed. Status:' . $result['errors']['code'];
                exit;
            }
        } else {
            echo 'Transaction canceled by user';
            exit;
        }

    }
    private function getDpAmount($Amount)
    {
        $config = $this->config->item('donate_zarinpal');

        $points = $config['values'];
        return $points[$Amount];
    }

    private function updateMonthlyIncome($price)
    {
        $query = $this->db->query("SELECT COUNT(*) AS `total` FROM monthly_income WHERE month=?", array(date("Y-m")));

        $row = $query->result_array();

        if($row[0]['total'])
        {
            $this->db->query("UPDATE monthly_income SET amount = amount + ".round($price)." WHERE month=?", array(date("Y-m")));
        }
        else
        {
            $this->db->query("INSERT INTO monthly_income(month, amount) VALUES(?, ?)", array(date("Y-m"), round($price)));
        }
    }
}