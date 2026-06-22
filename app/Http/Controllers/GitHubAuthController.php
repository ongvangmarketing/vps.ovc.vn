<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GitHubAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')
            ->scopes(['repo', 'read:user'])
            ->redirect();
    }

    public function callback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
            
            $user = Auth::user();
            if ($user) {
                // If user is already logged in, link github account
                $user->update([
                    'github_id' => $githubUser->getId(),
                    'github_token' => $githubUser->token,
                    'github_username' => $githubUser->getNickname(),
                ]);
            } else {
                // If not logged in, find or create
                $user = User::updateOrCreate(
                    ['github_id' => $githubUser->getId()],
                    [
                        'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                        'email' => $githubUser->getEmail() ?? $githubUser->getNickname() . '@github.local',
                        'github_token' => $githubUser->token,
                        'github_username' => $githubUser->getNickname(),
                        'password' => bcrypt(\Illuminate\Support\Str::random(24)) // random password
                    ]
                );
                Auth::login($user);
            }

            return redirect('/admin/websites/create');

        } catch (\Exception $e) {
            return redirect('/admin/websites/create')->with('error', 'GitHub authentication failed.');
        }
    }
}
