<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.title') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>{{ __('messages.title') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.id') }}</th>
                <th>{{ __('messages.type') }}</th>
                <th>{{ __('messages.amount') }}</th>
                <th>{{ __('messages.before') }}</th>
                <th>{{ __('messages.after') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.reason') }}</th>
                <th>{{ __('messages.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
                <tr>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->type }}</td>
                    <td>{{ number_format($transaction->amount, 2) }}</td>
                    <td>{{ number_format($transaction->balance_before, 2) }}</td>
                    <td>{{ number_format($transaction->balance_after, 2) }}</td>
                    <td>{{ $transaction->status }}</td>
                    <td>{{ $transaction->reason }}</td>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                </tr>
        </tbody>
    </table>
</body>
</html>
