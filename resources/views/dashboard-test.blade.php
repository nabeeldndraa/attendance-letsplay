<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Let's Play Indonesia</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f6f8;
            color: #222;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: #e30613;
            color: white;
            padding: 25px 15px;
        }

        .brand {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo {
            width: 55px;
            height: 55px;
            margin: 0 auto 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: white;
            color: #e30613;

            border-radius: 12px;

            font-size: 20px;
            font-weight: bold;
        }

        .brand h2 {
            font-size: 17px;
            margin-bottom: 5px;
        }

        .brand p {
            font-size: 12px;
            opacity: 0.8;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .menu a {
            color: white;
            text-decoration: none;

            padding: 13px 15px;

            border-radius: 7px;

            font-size: 14px;
        }

        .menu a:hover,
        .menu a.active {
            background: rgba(255, 255, 255, 0.15);
        }


        /* MAIN */
        .main {
            margin-left: 240px;
            min-height: 100vh;
        }


        /* HEADER */
        .header {
            height: 75px;

            background: white;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 35px;

            border-bottom: 1px solid #eee;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 14px;
            font-weight: bold;
        }

        .user-role {
            font-size: 12px;
            color: #888;
            margin-top: 3px;
        }

        .logout-btn {
            border: none;
            background: #e30613;
            color: white;

            padding: 10px 18px;

            border-radius: 6px;

            font-weight: bold;

            cursor: pointer;
        }

        .logout-btn:hover {
            background: #c80510;
        }


        /* CONTENT */
        .content {
            padding: 30px 35px;
        }


        /* WELCOME */
        .welcome {
            background: white;

            padding: 30px;

            border-radius: 10px;

            margin-bottom: 25px;

            border-left: 5px solid #e30613;
        }

        .welcome h1 {
            font-size: 26px;
            margin-bottom: 8px;
        }

        .welcome h1 span {
            color: #e30613;
        }

        .welcome p {
            color: #777;
            font-size: 14px;
        }


        /* CARDS */
        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .card {
            background: white;

            padding: 23px;

            border-radius: 10px;

            border: 1px solid #eee;
        }

        .card-title {
            color: #888;

            font-size: 13px;

            margin-bottom: 10px;
        }

        .card-value {
            font-size: 18px;
            font-weight: bold;
        }

        .status-active {
            color: #168a45;
        }


        /* ACTIVITY */
        .activity {
            background: white;

            margin-top: 25px;

            padding: 25px;

            border-radius: 10px;

            border: 1px solid #eee;
        }

        .activity h3 {
            margin-bottom: 18px;
        }

        .activity-item {
            padding: 15px 0;

            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item strong {
            display: block;
            margin-bottom: 5px;
        }

        .activity-item span {
            color: #888;
            font-size: 13px;
        }


        /* FOOTER */
        .footer {
            padding: 25px 35px;

            color: #999;

            font-size: 12px;
        }


        /* MOBILE */
        @media (max-width: 768px) {

            .sidebar {
                position: static;

                width: 100%;
                height: auto;

                padding: 20px 15px;
            }

            .brand {
                margin-bottom: 20px;
            }

            .menu {
                flex-direction: row;

                overflow-x: auto;
            }

            .menu a {
                white-space: nowrap;
            }

            .main {
                margin-left: 0;
            }

            .header {
                height: auto;

                padding: 15px 20px;

                gap: 15px;
            }

            .header-title {
                font-size: 17px;
            }

            .user-info {
                display: none;
            }

            .content {
                padding: 20px;
            }

            .cards {
                grid-template-columns: 1fr;
            }

            .welcome h1 {
                font-size: 22px;
            }

            .footer {
                padding: 20px;
            }
        }
    </style>
</head>

<body>


    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="brand">

            <div class="logo">
                LP
            </div>

            <h2>
                Let's Play Indonesia
            </h2>

            <p>
                Sistem Presensi Intern
            </p>

        </div>


        <nav class="menu">

            <a href="/dashboard-test" class="active">
                Dashboard
            </a>

            <a href="#">
                Presensi
            </a>

            <a href="#">
                Riwayat
            </a>

            <a href="#">
                Profil
            </a>

        </nav>

    </aside>



    <!-- MAIN -->

    <main class="main">


        <!-- HEADER -->

        <header class="header">

            <div class="header-title">
                Dashboard
            </div>


            <div class="header-right">

                <div class="user-info">

                    <div class="user-name">
                        {{ auth()->user()->nama }}
                    </div>

                    <div class="user-role">
                        {{ auth()->user()->role }}
                    </div>

                </div>


                <form action="{{ url('/logout') }}" method="POST">

                    @csrf

                    <button type="submit" class="logout-btn">
                        Logout
                    </button>

                </form>

            </div>

        </header>



        <!-- CONTENT -->

        <section class="content">


            <!-- WELCOME -->

            <div class="welcome">

                <h1>
                    Selamat Datang,
                    <span>{{ auth()->user()->nama }}</span>
                </h1>

                <p>
                    Selamat datang di Sistem Presensi Intern Let's Play Indonesia.
                </p>

            </div>



            <!-- INFORMATION CARDS -->

            <div class="cards">


                <div class="card">

                    <div class="card-title">
                        Status Akun
                    </div>

                    <div class="card-value status-active">
                        {{ auth()->user()->status_akun }}
                    </div>

                </div>



                <div class="card">

                    <div class="card-title">
                        Bidang Magang
                    </div>

                    <div class="card-value">
                        {{ auth()->user()->bidang_magang }}
                    </div>

                </div>



                <div class="card">

                    <div class="card-title">
                        Periode Magang
                    </div>

                    <div class="card-value">
                        {{ auth()->user()->tanggal_mulai }}
                        -
                        {{ auth()->user()->tanggal_selesai }}
                    </div>

                </div>


            </div>



            <!-- ACTIVITY -->

            <div class="activity">

                <h3>
                    Aktivitas
                </h3>


                <div class="activity-item">

                    <strong>
                        Login berhasil
                    </strong>

                    <span>
                        Anda berhasil masuk ke sistem.
                    </span>

                </div>


                <div class="activity-item">

                    <strong>
                        Sistem presensi
                    </strong>

                    <span>
                        Fitur presensi sedang dalam tahap pengembangan.
                    </span>

                </div>


            </div>


        </section>



        <!-- FOOTER -->

        <footer class="footer">

            Let's Play Indonesia © 2026

        </footer>


    </main>

</body>
</html>