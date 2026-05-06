<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //

    protected function blockDestructiveAction(\Illuminate\Http\Request $request, string $actionLabel = 'destructive action'): ?\Illuminate\Http\RedirectResponse
    {
        $allowed = filter_var(env('PIGSTEP_ALLOW_DESTRUCTIVE_ACTIONS', false), FILTER_VALIDATE_BOOLEAN);
        $expectedCode = trim((string) env('PIGSTEP_DESTRUCTIVE_CONFIRM_CODE', ''));
        $submittedCode = trim((string) (
            $request->input('destructive_confirm_code')
            ?: $request->header('X-Pigstep-Confirm-Code', '')
        ));

        if (!$allowed || $expectedCode === '' || !hash_equals($expectedCode, $submittedCode)) {
            return redirect()
                ->back()
                ->with('error', ucfirst($actionLabel) . ' blocked. Destructive actions require server-side confirmation.');
        }

        return null;
    }

}
