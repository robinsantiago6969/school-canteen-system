
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>School Canteen Ordering</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;800&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="{{ asset('css/canteen.css') }}">
</head>

<body>

    <header class="top">
        <div class="brand">
            <span class="logo">🍱</span>

            <div>
                <h1>School Canteen</h1>
                <p>Order ahead. Skip the line.</p>
            </div>
        </div>

        <nav>
            <button
                id="navStudent"
                class="active"
                onclick="showPage('student')"
            >
                Student
            </button>

            <button
                id="navStaff"
                onclick="showPage('staff')"
            >
                Canteen staff
            </button>
        </nav>
    </header>

    <main>

        <!-- STUDENT AUTHENTICATION -->
        <section id="authPage" class="page">

            <div class="auth card">

                <div class="tabs">
                    <button
                        id="tabLogin"
                        class="active"
                        onclick="authTab('login')"
                    >
                        Log in
                    </button>

                    <button
                        id="tabRegister"
                        onclick="authTab('register')"
                    >
                        Create account
                    </button>
                </div>

                <!-- LOGIN FORM -->
                <form id="loginForm">

                    <label>
                        Student ID
                        <input
                            id="studentID"
                            autocomplete="username"
                            placeholder="e.g. 202611"
                            required
                        >
                    </label>

                    <label>
                        Password
                        <input
                            id="studentPassword"
                            type="password"
                            autocomplete="current-password"
                            required
                        >
                    </label>

                    <button class="primary" type="submit">
                        Log in
                    </button>

                    <p id="loginMessage" class="msg"></p>

                    <small>Demo account: 202611 / demo123</small>

                </form>

                <!-- REGISTER FORM -->
                <form id="registerForm" class="hidden">

                    <label>
                        Full name
                        <input
                            id="regName"
                            autocomplete="name"
                            required
                        >
                    </label>

                    <div class="row">

                        <label>
                            Student ID
                            <input
                                id="regID"
                                placeholder="2026-050"
                                required
                            >
                        </label>

                        <label>
                            Year &amp; section
                            <input
                                id="regGrade"
                                placeholder="BSIS 1-1"
                                required
                            >
                        </label>

                    </div>

                    <label>
                        Password (at least 6 characters)
                        <input
                            id="regPass"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                    </label>

                    <label>
                        Confirm password
                        <input
                            id="regPass2"
                            type="password"
                            autocomplete="new-password"
                            required
                        >
                    </label>

                    <button class="primary" type="submit">
                        Create account
                    </button>

                    <p id="registerMessage" class="msg"></p>

                </form>

            </div>
        </section>

        <!-- STUDENT PAGE -->
        <section id="studentPage" class="page hidden">

            <div class="welcome card">

                <div>
                    <h2 id="welcomeName">Hi, Student!</h2>
                    <p id="welcomeGrade"></p>
                </div>

                <button class="ghost" onclick="logout()">
                    Log out
                </button>

            </div>

            <div class="layout">

                <!-- MENU -->
                <div>
                    <h3>Today's menu</h3>
                    <div id="menu" class="menu"></div>
                </div>

                <!-- CART -->
                <aside class="card cart">

                    <h3>Your order</h3>

                    <div id="cart"></div>

                    <label>
                        Pickup time

                        <select id="pickupTime">
                            <option>10:00 AM Recess</option>
                            <option>12:00 PM Lunch</option>
                            <option>3:00 PM Recess</option>
                        </select>
                    </label>

                    <!-- PAYMENT OPTIONS -->
                    <fieldset class="pay">

                        <legend>Payment</legend>

                        <label class="radio">
                            <input
                                type="radio"
                                name="pay"
                                value="gcash"
                                checked
                            >
                            Pay online with GCash
                        </label>

                        <label class="radio">
                            <input
                                type="radio"
                                name="pay"
                                value="cash"
                            >
                            Pay cash at the canteen
                        </label>

                        <!-- GCASH PAYMENT -->
                        <div id="gcashBox" class="gcash">

                            <img
                                id="gcashQR"
                                src="{{ asset('images/gcash-qr.png') }}"
                                alt="GCash QR code"
                                onerror="this.style.display='none';document.getElementById('qrMissing').classList.remove('hidden')"
                            >

                            <div id="qrMissing" class="qrmissing hidden">
                                Add your canteen QR image as public/images/gcash-qr.png.
                            </div>

                            <p>
                                Scan the QR, pay
                                <b>₱<span id="payAmount">0</span></b>,
                                then type the reference number from your receipt.
                            </p>

                            <label>
                                Amount sent (₱)
                                <input
                                    id="gcashAmt"
                                    type="number"
                                    min="1"
                                    step="1"
                                    placeholder="e.g. 12"
                                >
                            </label>

                            <label>
                                GCash reference no.
                                <input
                                    id="gcashRef"
                                    inputmode="numeric"
                                    placeholder="10–15 digits"
                                >
                            </label>

                            <label>
                                Payment screenshot (required)
                                <input
                                    id="gcashProof"
                                    type="file"
                                    accept="image/*"
                                >
                            </label>

                            <div id="gcashProofPreview" class="proof-preview hidden">

                                <img
                                    id="gcashProofImage"
                                    alt="Payment proof preview"
                                >

                                <button
                                    type="button"
                                    class="ghost"
                                    onclick="clearGcashProof()"
                                >
                                    Remove screenshot
                                </button>

                            </div>

                        </div>

                        <!-- CASH PAYMENT -->
                        <p id="cashNote" class="hidden">
                            Bring
                            <b>₱<span id="cashAmount">0</span></b>
                            in cash at pickup.
                        </p>

                    </fieldset>

                    <!-- ORDER TOTAL -->
                    <div class="total">
                        <span>Total</span>

                        <strong>
                            ₱<span id="total">0</span>
                        </strong>
                    </div>

                    <button class="primary" id="placeOrderBtn">
                        Place order
                    </button>

                    <p id="orderMessage" class="msg"></p>

                </aside>

            </div>

            <!-- STUDENT ORDER HISTORY -->
            <h3>My orders</h3>
            <div id="studentOrder"></div>

        </section>

        <!-- STAFF PAGE -->
        <section id="staffPage" class="page hidden">

            <!-- STAFF LOGIN -->
            <div id="staffGate" class="auth card">

                <h2>Staff access</h2>

                <form id="staffLoginForm">

                    <label>
                        Staff PIN
                        <input
                            id="staffPin"
                            type="password"
                            required
                        >
                    </label>

                    <button class="primary" type="submit">
                        Open dashboard
                    </button>

                    <p id="staffMessage" class="msg"></p>

                </form>

            </div>

            <!-- STAFF DASHBOARD -->
            <div id="staffBoard" class="hidden">

                <div class="staff-head">

                    <h2>Canteen dashboard</h2>

                    <button class="ghost" onclick="staffLogout()">
                        Staff logout
                    </button>

                </div>

                <!-- DASHBOARD STATISTICS -->
                <div class="stats">

                    <div class="card">
                        <h3 id="orderCount">0</h3>
                        <p>Total orders</p>
                    </div>

                    <div class="card">
                        <h3 id="pendingCount">0</h3>
                        <p>Waiting to be prepared</p>
                    </div>

                    <div class="card">
                        <h3 id="readyCount">0</h3>
                        <p>Ready for pickup</p>
                    </div>

                </div>

                <!-- INCOMING ORDERS -->
                <h3>Incoming orders</h3>
                <div id="orders"></div>

                <!-- SALES AND ORDER HISTORY -->
                <h3>Sales &amp; order history</h3>

                <label class="range">
                    Show

                    <select id="range">
                        <option value="today">Today</option>
                        <option value="week">Last 7 days</option>
                        <option value="all">All time</option>
                    </select>
                </label>

                <!-- CALENDAR FILTER -->
                <div class="calendar-filter card">

                    <label for="salesDate">
                        View sales for a specific day
                    </label>

                    <div class="calendar-controls">

                        <input id="salesDate" type="date">

                        <button
                            type="button"
                            class="ghost"
                            onclick="clearSalesDate()"
                        >
                            Clear date
                        </button>

                    </div>

                    <p id="salesDateNote" class="empty">
                        Choose a date to view that day's orders.
                    </p>

                </div>

                <div id="sales" class="stats"></div>

                <div id="sold" class="chips"></div>

                <div class="tablewrap">
                    <table id="history"></table>
                </div>

                <!-- FOOD AVAILABILITY -->
                <h3>Food availability</h3>
                <div id="availability" class="avail"></div>

            </div>

        </section>

    </main>

    <!-- FOOTER -->
    <footer>
        <p>
            School Canteen Ordering System © {{ date('Y') }}
        </p>
    </footer>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('js/canteen.js') }}"></script>

</body>
</html>
