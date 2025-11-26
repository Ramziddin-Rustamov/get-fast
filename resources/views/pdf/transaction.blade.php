<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>{!! $t['title'] !!}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 10px 0;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
            border-bottom: 2px solid #4CAF50;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .icon {
            width: 16px;
            height: 16px;
            display: inline-block;
            vertical-align: middle;
            margin-right: 4px;
        }

        /* Simple icons using Unicode emojis */
        .id-icon::before { content: "🆔"; }
        .type-icon::before { content: "📦"; }
        .amount-icon::before { content: "💰"; }
        .before-icon::before { content: "⬅️"; }
        .after-icon::before { content: "➡️"; }
        .status-icon::before { content: "✅"; }
        .reason-icon::before { content: "📝"; }
        .created-icon::before { content: "📅"; }
    </style>
</head>
<body>
    <h2>{!! $t['title'] !!}</h2>
    <table>
        <thead>
            <tr>
                <th><span class="icon id-icon"></span> {!! $t['id'] !!}</th>
                <th><span class="icon type-icon"></span> {!! $t['type'] !!}</th>
                <th><span class="icon amount-icon"></span> {!! $t['amount'] !!}</th>
                <th><span class="icon before-icon"></span> {!! $t['before'] !!}</th>
                <th><span class="icon after-icon"></span> {!! $t['after'] !!}</th>
                <th><span class="icon status-icon"></span> {!! $t['status'] !!}</th>
                <th><span class="icon reason-icon"></span> {!! $t['reason'] !!}</th>
                <th><span class="icon created-icon"></span> {!! $t['created_at'] !!}</th>
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
