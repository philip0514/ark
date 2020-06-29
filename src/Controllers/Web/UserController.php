<?php
namespace Philip0514\Ark\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use Philip0514\Ark\Repositories\Web\UserRepository;
use Philip0514\Ark\Repositories\Web\PageRepository;

//trait
use Philip0514\Ark\Traits\SessionTrait;
use Philip0514\Ark\Traits\Helper;

class UserController extends Controller
{
    use SessionTrait, Helper;

    protected 	$repo;

    public function __construct()
    {
        $this->repo = new \stdClass();
        $this->repo->user = new UserRepository();
        $this->repo->page = new PageRepository();
    }

    public function login(Request $request)
    {
        $this->setReferralUrl();
        $data = $this->repo->page->get('login');

        $data['html'] = $this->bladeHtml($data['html'], [
            'login_failed'  =>  session()->has('status')
        ]);

        return view('ark::Web.welcome.index', $data);
    }

    public function logout(Request $request)
    {
        session()->forget('password_token');

        return redirect(route('index'));
    }

    public function loginProcess(Request $request)
    {
        $input = $request->except(['_token']);
        $username = $request->input('username', null);
        $password = $request->input('password', null);

        $result = $this->repo->user->login($username, $password);

        if(!$result['success']) {
            return redirect( route('login') )->with('status', 'login_failed');
        }
        $url = $this->getReferralUrl();

        session()->put('password_token', $result['data']['token']['access_token']);
        session()->put('password_token_expires', time()+$result['data']['token']['expires_in']);

        return redirect($url);
    }

    public function register(Request $request)
    {
        $this->setReferralUrl();
        $data = $this->repo->page->get('register');

        return view('ark::Web.welcome.index', $data);
    }

    public function registerProcess(Request $request)
    {
        $input = $request->except(['_token']);
        $name = $request->input('name', null);
        $username = $request->input('username', null);
        $password = $request->input('password', null);

        $result = $this->repo->user->register($username, $password, $name);

        if(!$result['success']) {
            $errors = [];
            foreach ($result['error'] as $key => $value) {
                $errors[] = $value;
            }
            return back()->withInput($input)->withErrors($errors);
        }

        $url = route('register_completed');
        //Cookie::queue('password_token', $result['data']['token']['access_token'], $result['data']['token']['expires_in']);
        //Cookie::queue(Cookie::forget('client_token'));

        session()->put('password_token', $result['data']['token']['access_token']);
        session()->put('password_token_expires', time()+$result['data']['token']['expires_in']);

        return redirect($url);
    }

    public function registerValidate(Request $request)
    {
        $username = $request->input('username', null);

        $result = $this->repo->user->registerValidate($username);

        $exist = $result['data']['exist'];

        if($exist){
            return 'false';
        }else{
            return 'true';
        }
    }

    public function registerCompleted(Request $request)
    {
        $data = $this->repo->page->get('register/complete');

        return view('ark::Web.welcome.index', $data);
    }
}