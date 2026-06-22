<?php

namespace App\Http\Controllers;

use App\Jobs\DeployWebsiteJob;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GithubWebhookController extends Controller
{
    public function handle(Request $request, string $domain)
    {
        $website = Website::where('domain', $domain)->firstOrFail();

        if (!$website->auto_deploy) {
            return response()->json(['message' => 'Auto deploy is disabled.'], 200);
        }

        // Validate signature if secret is set
        if ($website->webhook_secret) {
            $signature = $request->header('X-Hub-Signature-256');
            if (!$signature) {
                return response()->json(['message' => 'Missing signature.'], 403);
            }

            $payload = $request->getContent();
            $hash = 'sha256=' . hash_hmac('sha256', $payload, $website->webhook_secret);

            if (!hash_equals($hash, $signature)) {
                return response()->json(['message' => 'Invalid signature.'], 403);
            }
        }

        // Only deploy on push events
        $event = $request->header('X-GitHub-Event');
        if ($event !== 'push') {
            return response()->json(['message' => 'Ignored event.'], 200);
        }

        // Check branch
        $ref = $request->input('ref');
        $branch = str_replace('refs/heads/', '', $ref);

        if ($branch !== $website->branch) {
            return response()->json(['message' => "Ignored branch push ($branch)."], 200);
        }

        // Dispatch deploy job
        DeployWebsiteJob::dispatch($website, 'webhook');

        Log::info("Webhook triggered deploy for {$website->domain}");

        return response()->json(['message' => 'Deploy queued successfully.']);
    }
}
