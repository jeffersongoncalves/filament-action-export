<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Export' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 6px 10px;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    @isset($companyName)
        <div class="company-name">{{ $companyName }}</div>
    @endisset

    <h1>{{ $title ?? 'Export' }}</h1>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $record)
                <tr>
                    @foreach (array_keys($columns) as $key)
                        <td>{{ data_get($record, $key, '') }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" style="text-align: center; padding: 20px;">
                        {{ __('filament-action-export::filament-action-export.messages.no_records') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
