    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 640px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .email-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }

        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }

        .success-banner {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .party-table,
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .party-table td {
            width: 50%;
            vertical-align: top;
            padding: 12px;
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            font-size: 14px;
        }

        .party-table td+td {
            border-left: 4px solid #fecaca;
        }

        .party-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #b91c1c;
            margin-bottom: 6px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .meta-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .meta-table td:first-child {
            font-weight: 600;
            color: #666;
            width: 45%;
        }

        .meta-table td:last-child {
            text-align: right;
        }

        .items-table {
            margin: 20px 0 0;
            font-size: 14px;
        }

        .items-table th {
            background-color: #dc2626;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }

        .items-table th.num,
        .items-table td.num {
            text-align: right;
            white-space: nowrap;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .total-row td {
            background-color: #fef2f2;
            font-weight: 700;
            font-size: 16px;
            color: #dc2626;
            border-bottom: none;
        }

        .highlight {
            color: #dc2626;
            font-weight: 600;
        }

        .cta-section {
            text-align: center;
            margin: 30px 0 10px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }

        .footer {
            background-color: #fef2f2;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #fecaca;
        }

        @media (max-width: 600px) {
            .party-table td {
                display: block;
                width: auto;
            }

            .party-table td+td {
                margin-top: 10px;
            }
        }
    </style>
