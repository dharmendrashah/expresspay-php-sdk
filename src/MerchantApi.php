<?php

namespace Expay\SDK;

use Exception;
use GuzzleHttp\Client;
use Expay\SDK\Utility\Config;
use Expay\SDK\Exceptions\BadRequest;
use Expay\SDK\Requests\QueryInvoice;
use Expay\SDK\Requests\SubmitInvoice;

/**
 * MerchantApi
 */
class MerchantApi
{
  /**
   * client
   *
   * @var string
   */
  private $client = "";  
  /**
   * config
   *
   * @var string
   */
  private $config = "";  
  /**
   * env
   *
   * @var string
   */
  private $env = "";    
  /**
   * base_url
   *
   * @var string
   */
  private $base_url = "";
  /**
   * allowed_envs
   *
   * @var array
   */
  protected $allowed_envs = ["sandbox", "production"];
    
  /**
   * __construct
   *
   * @param  mixed $merchant_id
   * @param  mixed $merchant_key
   * @return void
   */
  public function __construct(string $merchant_id, string $merchant_key, string $environment)
  {
    // set required obj vars
    $this->env = $environment;
    $this->config = new Config($merchant_id, $merchant_key);
    
    // init and validate env
    $this->init();
  }
  
  /**
   * init
   *
   * @return int
   */
  private function init() : int
  {
    // check env
    if (!in_array($this->env, $this->allowed_envs))
    {
      throw new BadRequest("Sorry, (" . $this->env . ") is not allowed, expecting (sandbox) or (production)");
    }

    // get api url
    if ($this->env === "sandbox")
    {
      $this->base_url = $this->config->get_sandbox_url();
      $this->client = new Client(['base_uri' => $this->config->get_sandbox_url()]);
    }
    elseif ($this->env === "production")
    {
      $this->base_url = $this->config->get_production_url();
      $this->client = new Client(['base_uri' => $this->config->get_production_url()]);
    }

    return 0;
  }

  /**
   * submit
   *
   * @param  mixed $currency
   * @param  mixed $amount
   * @param  mixed $order_id
   * @param  mixed $order_desc
   * @param  mixed $redirect_url
   * @param  mixed $account_number
   * @param  mixed $order_img_url
   * @param  mixed $first_name
   * @param  mixed $last_name
   * @param  mixed $phone_number
   * @param  mixed $email
   * @return array
   */
  public function submit(string $currency, float $amount, string $order_id, string $order_desc, string $redirect_url, string $account_number, string $order_img_url = null, string $first_name = null, string $last_name = null, string $phone_number = null, string $email = null) : array
  {
    try {
      $func_vars = get_defined_vars();

      $requestAccessor = new SubmitInvoice($func_vars, $this->config);
      $request = $requestAccessor->make()->toArray();

      $response = $this->client->request("POST", "submit.php", ["form_params" => $request]);

      return json_decode($response->getBody(), true);

    } catch(Exception $e) {
      throw new BadRequest($e->getMessage());
    }
  }
  
  /**
   * checkout
   *
   * @param  mixed $token
   * @return string
   */
  public function checkout(string $token) : string
  {
    try {
      return sprintf("%scheckout.php?token=%s", $this->base_url, $token);
    } catch(Exception $e) {
      throw new BadRequest($e->getMessage());
    }
  }
  
  /**
   * query
   *
   * @param  mixed $token
   * @return array
   */
  public function query(string $token) : array
  {
    try {
      $func_vars = get_defined_vars();

      $requestAccessor = new QueryInvoice($func_vars, $this->config);
      $request = $requestAccessor->make()->toArray();

      $response = $this->client->request("POST", "query.php", ["form_params" => $request]);

      return json_decode($response->getBody(), true);

    } catch(Exception $e) {
      throw new BadRequest($e->getMessage());
    }
  }
}