<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoicesRequest;
use App\Services\InvoicesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoicesController extends Controller
{
    private $invoicesService;

    public function __construct(InvoicesService $invoicesService)
    {
        $this->invoicesService = $invoicesService;
    }

    public function index()
    {
        $invoices = $this->invoicesService->index();

        return view('pages.admin.invoices.index')->with(compact(['invoices']));
    }

    public function generate(InvoicesRequest $request)
    {
        $request->validated();

        $get_billings = DB::table('billings')->get();

        foreach ($get_billings as $billings) {
            $network = strtolower($billings->network);
            switch ($network) {
                case 'facebook':
                    $facebook_sessions = DB::table('conversation_sessions')->where('terminate', '=', 1)->where('channel', '=',$network)->whereBetween('created_at', [$request['ini_date'], $request['end_date']])->count();
                    $get_billing_facebook = DB::table('billings')->where('network', '=', $network)->first();
                    $totals[$network] = $facebook_sessions * $get_billing_facebook->price;
                    break;
                case 'twitter':
                    $twitter_sessions = DB::table('conversation_sessions')->where('terminate', '=', 1)->where('channel', '=', $network)->whereBetween('created_at', [$request['ini_date'], $request['end_date']])->count();
                    $get_billing_twitter = DB::table('billings')->where('network', '=', $network)->first();
                    $totals[$network] = $twitter_sessions * $get_billing_twitter->price;
                    break;
                case 'whatsapp':
                    $whatsapp_sessions = DB::table('conversation_sessions')->where('terminate', '=', 1)->where('channel', '=', $network)->whereBetween('created_at', [$request['ini_date'], $request['end_date']])->count();
                    $get_billing_whatsapp = DB::table('billings')->where('network', '=', $network)->first();
                    $totals[$network] = $whatsapp_sessions * $get_billing_whatsapp->price;
                    break;
                default:
                    break;
            }
        }

        $get_company = DB::table('companies')->first();

        $get_address = DB::table('addresses')->where('id', '=', $get_company->address_id)->first();

        $subtotal = array_sum($totals);

        $full_total = $subtotal;

        $invoice_id = random_int(1000000, 9999999);

        $invoice_obj = (object)array_merge_recursive((array)$get_company, (array)$get_address, ['facebook_sessions' => $facebook_sessions, 'twitter_sessions' => $twitter_sessions, 'whatsapp_sessions' => $whatsapp_sessions, 'get_billing_facebook' => $get_billing_facebook, 'get_billing_twitter' => $get_billing_twitter, 'get_billing_whatsapp' => $get_billing_whatsapp, 'invoice_id' => $invoice_id, 'date_ini' => $request['ini_date'], 'date_end' => $request['end_date'], 'total' => json_encode($totals), 'subtotal' => $subtotal,'full_total' => $full_total]);

        $invoice_obj->birthday = date('d/m/Y',strtotime($invoice_obj->birthday));
        $invoice_obj->date_ini = date('d/m/Y',strtotime($invoice_obj->date_ini));
        $invoice_obj->date_end = date('d/m/Y',strtotime($invoice_obj->date_end));

        $invoices = $invoice_obj;

        $insert_params = [
            'address_id'    => $invoices->address_id,
            'number_home'   => $invoices->number_home,
            'name'          => $invoices->name,
            'email'         => $invoices->email,
            'cellphone'     => $invoices->cellphone,
            'cpf_cnpj'      => $invoices->cpf_cnpj,
            'birthday'      => $invoices->birthday,
            'street'        => $invoices->street,
            'neighborhood'  => $invoices->neighborhood,
            'city'          => $invoices->city,
            'uf'            => $invoices->uf,
            'postcode'      => $invoices->postcode,
            'invoice_id'    => $invoices->invoice_id,
            'date_ini'      => $invoices->date_ini,
            'date_end'      => $invoices->date_end,
            'total'         => $invoices->total,
            'subtotal'      => $invoices->subtotal,
            'full_total'    => $invoices->full_total,
            'created_at'    => Carbon::now()
        ];

        DB::table('invoices')->insert($insert_params);

        $invoice = DB::table('invoices')->where('invoice_id', '=', $invoice_id)->orderBy('id','desc')->first();
        $invoices->total = json_decode($invoice->total);

        return view('pages.admin.invoices.generate')->with(compact(['invoices']));
    }
}
