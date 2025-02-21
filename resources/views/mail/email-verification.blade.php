<div>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Verification</title>

        <style>
            body {
                background-color: #f7f7f7;
                /* bg-gray-100 equivalent */
                font-family: sans-serif;
                /* font-sans equivalent */
            }

            .container {
                margin-left: auto;
                margin-right: auto;
                padding-left: 1rem;
                /* px-4 equivalent */
                padding-right: 1rem;
                /* px-4 equivalent */
                padding-top: 2rem;
                /* py-8 equivalent */
                padding-bottom: 2rem;
                /* py-8 equivalent */
                max-width: 768px;
                /* Example max-width for larger screens if you want */
            }

            .bg-white {
                background-color: #ffffff;
            }

            .rounded-lg {
                border-radius: 0.5rem;
                /* rounded-lg equivalent */
            }

            .shadow-md {
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                /* shadow-md equivalent */
            }

            .p-8 {
                padding: 2rem;
                /* p-8 equivalent */
            }

            .text-2xl {
                font-size: 1.5rem;
                /* text-2xl equivalent */
                line-height: 2rem;
                /* text-2xl equivalent */
            }

            .font-bold {
                font-weight: 700;
                /* font-bold equivalent */
            }

            .mb-4 {
                margin-bottom: 1rem;
                /* mb-4 equivalent */
            }

            .bg-gray-200 {
                background-color: #e5e7eb;
                /* bg-gray-200 equivalent */
            }

            .rounded-lg {
                /* Already defined above, but included for clarity */
                border-radius: 0.5rem;
            }

            .py-2 {
                padding-top: 0.5rem;
                /* py-2 equivalent */
                padding-bottom: 0.5rem;
                /* py-2 equivalent */
            }

            .px-4 {
                padding-left: 1rem;
                /* px-4 equivalent */
                padding-right: 1rem;
                /* px-4 equivalent */
            }

            .text-center {
                text-align: center;
            }

            .text-xl {
                font-size: 1.25rem;
                /* text-xl equivalent */
                line-height: 1.75rem;
                /* text-xl equivalent */
            }

            .mb-6 {
                margin-bottom: 1.5rem;
                /* mb-6 equivalent */
            }

            .text-gray-500 {
                color: #71717a;
                /* text-gray-500 equivalent */
            }

            .text-sm {
                font-size: 0.875rem;
                /* text-sm equivalent */
                line-height: 1.25rem;
                /* text-sm equivalent */
            }
        </style>

    </head>

    <body class="bg-gray-100 font-sans">

        <div class="container mx-auto px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold mb-4">Welcome!</h2>

                @if($user) {{-- Conditionally display user name if available --}}
                <p class="mb-4">Hello, {{ $user }}!</p>
                @else
                <p class="mb-4">Hello!</p>
                @endif


                <p class="mb-4">Thank you for registering. Please verify your email address by entering the following
                    One-Time Password (OTP):</p>

                <div class="bg-gray-200 rounded-lg py-2 px-4 text-center text-xl font-bold mb-6">{{ $otp }}</div>

                <p class="mb-4">This OTP is valid for a limited time. If you do not verify your email within this time,
                    you
                    may need to request a new OTP.</p>

                <p class="mb-4">If you have any questions, please don't hesitate to contact us.</p>

                <p class="mb-4">Sincerely,<br>
                    The Our Platform Team</p>

                <div class="text-center text-gray-500 text-sm">
                    <p>&copy; {{ date('Y') }} Our Platform. All rights reserved.</p>
                </div>

            </div>
        </div>

    </body>

    </html>
</div>
