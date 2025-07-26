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
            @foreach($transactions as $tx)
                <tr>
                    <td>{{ $tx->id }}</td>
                    <td>{{ $tx->type }}</td>
                    <td>{{ number_format($tx->amount, 2) }}</td>
                    <td>{{ number_format($tx->balance_before, 2) }}</td>
                    <td>{{ number_format($tx->balance_after, 2) }}</td>
                    <td>{{ $tx->status }}</td>
                    <td>{{ $tx->reason }}</td>
                    <td>{{ $tx->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
