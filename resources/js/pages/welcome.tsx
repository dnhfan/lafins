import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>

            {/* this is header */}
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl">
                    <nav className="flex items-center justify-end gap-4">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                {/* this is main content */}
                <div className="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0">
                    <main className="flex w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
                        <div className="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            <h1 className="mb-1 font-medium">
                                Chào mừng đến với Lafins
                            </h1>
                            <p className="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                                Lafins — Ứng dụng quản lý tài chính cá nhân giúp
                                bạn quản lý thu chi, phân bổ ngân sách và theo
                                dõi mục tiêu tài chính một cách trực quan.
                            </p>
                            <ul className="mb-4 flex flex-col lg:mb-6">
                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-1/2 before:bottom-0 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Quản lý thu chi: Ghi chép chi tiết các
                                        khoản thu và chi hàng ngày.
                                    </span>
                                </li>
                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-1/2 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Phân chia quỹ (Jars): Tạo và phân bổ
                                        ngân sách cho các mục tiêu khác nhau.
                                    </span>
                                </li>
                                <li className="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-1/2 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]">
                                    <span className="relative bg-white py-1 dark:bg-[#161615]">
                                        <span className="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                                            <span className="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]" />
                                        </span>
                                    </span>
                                    <span>
                                        Báo cáo & biểu đồ: Theo dõi xu hướng chi
                                        tiêu và tiến độ mục tiêu.
                                    </span>
                                </li>
                            </ul>
                            <ul className="flex gap-3 text-sm leading-normal">
                                <li>
                                    <Link
                                        href={auth.user ? dashboard() : login()}
                                        className="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                                    >
                                        Bắt đầu ngay
                                    </Link>
                                </li>
                            </ul>
                        </div>
                        <div className="relative -mb-px aspect-[335/376] w-full shrink-0 overflow-hidden rounded-t-lg bg-[#fff2f2] lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-[#1D0002]">
                            <div className="flex h-full w-full items-center justify-center p-6 lg:p-8">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 900 1578"
                                    preserveAspectRatio="xMidYMid meet"
                                    className="max-h-[320px] w-auto lg:max-h-[560px]"
                                >
                                    <metadata>
                                        Created by potrace 1.10, written by
                                        Peter Selinger 2001-2011
                                    </metadata>
                                    <g
                                        transform="translate(0.000000,1578.000000) scale(0.100000,-0.100000)"
                                        fill="#000000"
                                        stroke="none"
                                        className="fill-[#000000] dark:fill-[#EDEDEC]"
                                    >
                                        <path
                                            d="M4225 11189 c-93 -4 -195 -12 -225 -18 -30 -6 -86 -15 -125 -22 -394
                                -64 -819 -237 -1220 -497 -373 -242 -770 -659 -1017 -1067 -170 -282 -334
                                -650 -403 -905 -10 -36 -23 -85 -30 -110 -39 -138 -85 -419 -106 -655 -16
                                -173 -23 -598 -11 -705 34 -300 38 -331 71 -485 35 -162 59 -256 76 -305 7
                                -19 22 -64 33 -100 50 -162 122 -339 207 -515 246 -504 589 -938 987 -1248
                                176 -137 403 -279 563 -352 169 -78 193 -88 275 -120 25 -9 56 -21 68 -26 33
                                -13 136 -46 202 -65 30 -9 69 -20 85 -25 172 -54 637 -106 845 -96 220 11 412
                                26 473 37 149 27 274 52 302 60 17 5 66 18 110 29 114 29 172 47 235 71 30 12
                                66 25 80 30 52 17 265 112 363 162 175 89 327 184 490 306 116 87 114 96 -27
                                102 -89 4 -111 8 -120 22 -9 15 -4 25 31 59 49 50 82 58 256 58 65 1 126 5
                                136 11 33 17 243 252 234 261 -4 4 -65 10 -133 11 -121 3 -125 4 -128 25 -5
                                34 44 91 87 102 20 5 96 13 169 17 104 5 137 10 155 24 29 23 164 219 169 245
                                3 19 -3 20 -129 20 -160 0 -169 5 -133 71 43 78 44 79 209 82 113 3 150 7 163
                                19 18 16 154 287 160 318 4 25 -44 33 -190 34 -115 1 -122 5 -98 62 28 68 43
                                74 190 77 72 1 140 4 152 7 22 5 37 31 61 105 56 176 77 272 60 282 -6 4 -67
                                8 -134 8 -124 0 -173 9 -173 32 0 30 23 91 41 109 17 17 34 19 159 19 134 0
                                140 1 154 23 17 25 46 211 46 294 0 64 5 62 -175 66 l-130 2 -3 52 c-5 92 -4
                                93 163 93 112 0 147 3 158 14 33 34 33 350 -1 378 -10 9 -57 14 -151 15 -116
                                2 -137 5 -148 19 -18 25 -17 109 2 124 8 7 67 14 150 17 123 5 137 8 147 26
                                11 21 8 74 -12 231 -19 147 -5 136 -174 136 -163 0 -159 -2 -181 81 -21 77
                                -17 79 141 79 109 0 135 3 149 16 9 10 13 24 10 33 -4 9 -9 32 -12 51 -8 54
                                -74 283 -85 298 -8 9 -53 12 -164 12 l-153 0 -26 55 c-43 90 -43 90 125 93 79
                                1 148 6 153 11 13 13 -121 327 -146 343 -7 4 -81 8 -165 8 l-152 0 -31 48
                                c-52 82 -47 86 118 92 78 3 145 8 148 12 9 8 -24 67 -112 200 l-64 97 -142 -4
                                c-78 -2 -152 -2 -164 2 -33 8 -99 101 -84 116 8 8 59 13 139 15 70 2 131 7
                                135 11 10 10 -94 149 -152 205 l-40 39 -166 1 -167 1 -34 39 c-19 22 -34 45
                                -34 53 0 20 44 33 115 33 71 0 115 13 115 33 0 24 -300 253 -455 347 -156 95
                                -426 226 -590 286 -229 84 -462 150 -665 187 -234 43 -267 47 -418 52 -81 3
                                -181 7 -222 8 -41 2 -151 0 -245 -4z m365 -369 c411 -34 823 -163 1160 -363
                                134 -80 194 -120 296 -196 389 -290 706 -677 926 -1131 179 -368 272 -706 328
                                -1190 12 -107 12 -679 0 -780 -48 -388 -135 -730 -260 -1024 -131 -310 -360
                                -670 -589 -931 -175 -198 -441 -423 -663 -561 -96 -59 -398 -214 -418 -214 -7
                                0 -24 -7 -38 -15 -84 -47 -386 -127 -632 -167 -118 -19 -542 -15 -665 7 -55 9
                                -122 21 -150 25 -203 34 -476 129 -725 255 -536 269 -1017 752 -1314 1317
                                -163 309 -283 647 -341 958 -9 47 -20 105 -25 130 -27 135 -35 274 -35 595 0
                                322 8 462 35 595 5 25 17 88 26 140 9 52 28 136 41 185 14 50 28 106 33 125
                                32 137 148 427 256 640 199 393 577 831 944 1092 372 266 771 426 1230 493
                                160 23 403 30 580 15z"
                                        />
                                        <path
                                            d="M4135 10670 c-195 -17 -460 -77 -615 -138 -14 -5 -45 -18 -70 -27
                                -156 -62 -337 -154 -475 -243 -245 -158 -523 -421 -726 -687 -345 -451 -587
                                -1073 -646 -1660 -12 -122 -10 -636 4 -760 12 -119 36 -269 52 -325 5 -19 17
                                -69 26 -110 51 -220 146 -484 254 -700 70 -141 87 -171 177 -315 21 -33 83
                                -121 138 -195 110 -148 375 -424 506 -526 41 -32 77 -61 80 -64 8 -10 176
                                -122 254 -170 77 -47 281 -145 371 -179 33 -13 76 -29 95 -36 19 -8 62 -21 95
                                -30 33 -10 71 -21 85 -25 40 -14 188 -47 285 -64 229 -40 714 -23 885 32 19 6
                                64 18 100 28 107 28 176 52 334 116 78 32 305 152 384 204 352 231 618 491
                                857 835 126 183 282 481 367 704 99 258 145 454 199 835 19 131 16 657 -5 805
                                -25 180 -48 320 -66 385 -5 19 -19 73 -31 120 -25 100 -99 326 -127 390 -42
                                98 -65 148 -95 210 -281 586 -754 1061 -1338 1343 -146 71 -170 80 -314 128
                                -140 47 -181 57 -460 109 -76 14 -461 21 -580 10z m-837 -991 c31 -18 82 -46
                                112 -64 30 -18 199 -115 375 -215 176 -100 330 -191 343 -203 l22 -20 0 -1009
                                c0 -872 2 -1009 14 -1014 8 -3 54 17 101 45 48 28 88 51 90 51 2 0 60 34 130
                                75 69 41 128 75 129 75 10 0 225 125 240 140 18 17 19 45 23 507 4 467 5 489
                                24 509 11 12 32 27 47 35 15 8 85 47 156 87 71 39 130 72 131 72 2 0 60 34
                                130 75 69 41 128 75 130 75 1 0 56 32 121 71 100 59 124 70 152 65 32 -5 105
                                -44 482 -261 272 -156 394 -231 422 -258 l28 -29 0 -509 c0 -320 -4 -517 -10
                                -529 -5 -10 -45 -38 -87 -62 -43 -23 -87 -48 -98 -55 -11 -7 -27 -16 -35 -20
                                -8 -4 -62 -35 -120 -69 -107 -63 -124 -73 -317 -182 -61 -35 -118 -71 -126
                                -80 -12 -14 -16 -96 -22 -487 -3 -258 -10 -475 -13 -482 -8 -14 -72 -56 -322
                                -210 -102 -63 -203 -127 -225 -142 -22 -14 -47 -29 -55 -34 -8 -4 -27 -15 -42
                                -25 -83 -53 -925 -556 -989 -591 -67 -36 -83 -41 -142 -41 l-65 0 -237 143
                                c-129 78 -246 148 -258 155 -12 7 -34 21 -49 30 -15 9 -114 70 -220 135 -106
                                65 -238 147 -293 183 -55 35 -109 68 -120 74 -11 5 -88 54 -172 107 -170 110
                                -232 149 -283 178 -88 51 -122 78 -131 103 -11 28 -3 2307 10 2828 6 246 7
                                254 29 276 12 12 210 136 440 275 469 283 451 276 550 222z"
                                        />
                                        <path
                                            d="M3080 9459 c-63 -39 -124 -75 -135 -80 -11 -5 -33 -18 -50 -29 -16
                                -11 -39 -24 -50 -30 -11 -5 -27 -15 -35 -20 -8 -6 -76 -45 -150 -88 -74 -44
                                -136 -86 -138 -94 -2 -11 27 -33 90 -69 51 -30 102 -59 113 -66 11 -7 29 -16
                                40 -22 11 -5 37 -21 58 -35 21 -14 40 -26 42 -26 2 0 50 -28 107 -61 57 -34
                                135 -79 175 -101 l72 -39 213 129 c117 71 268 162 335 202 67 40 127 78 134
                                86 8 10 -1 21 -45 49 -30 19 -57 35 -60 35 -2 0 -37 19 -78 43 -40 24 -80 46
                                -88 50 -8 4 -28 16 -45 27 -16 10 -41 25 -55 33 -14 8 -90 51 -170 95 -80 45
                                -149 82 -155 82 -5 -1 -62 -32 -125 -71z"
                                        />
                                        <path
                                            d="M2446 8918 c-3 -7 -3 -630 -3 -1384 2 -1311 3 -1373 20 -1392 10 -11
                                87 -62 170 -112 84 -51 179 -109 212 -130 33 -20 109 -66 168 -101 139 -83
                                135 -80 182 -109 23 -14 50 -30 60 -37 131 -80 711 -429 717 -431 4 -2 7 162
                                6 364 l-3 367 -45 32 c-42 29 -293 180 -630 378 -80 46 -153 93 -162 103 -15
                                17 -17 107 -23 1039 -6 926 -8 1022 -23 1038 -9 10 -145 95 -302 189 -157 94
                                -292 177 -301 184 -20 17 -38 18 -43 2z"
                                        />
                                        <path
                                            d="M3910 8915 c-7 -8 -16 -15 -20 -15 -7 0 -114 -63 -240 -140 -8 -6
                                -89 -53 -180 -106 -91 -54 -168 -102 -172 -108 -11 -16 -9 -1868 1 -1885 7
                                -12 27 -4 103 39 51 29 112 64 135 77 24 13 109 63 190 111 82 48 165 96 186
                                107 22 11 44 31 50 45 9 19 11 266 9 958 l-2 932 -24 0 c-13 0 -29 -7 -36 -15z"
                                        />
                                        <path
                                            d="M5697 8814 c-21 -14 -109 -64 -195 -111 -86 -48 -184 -103 -217 -123
                                -33 -20 -81 -46 -107 -59 -30 -14 -45 -27 -42 -36 3 -7 80 -57 172 -111 195
                                -114 420 -252 443 -271 30 -25 70 -14 171 47 54 33 101 60 103 60 1 0 59 33
                                127 73 67 41 150 89 183 107 64 35 95 58 95 71 0 7 -333 208 -480 291 -180
                                101 -190 103 -253 62z"
                                        />
                                        <path
                                            d="M5062 8298 c-14 -14 -18 -714 -4 -735 6 -10 157 -103 166 -103 3 0
                                50 -27 106 -60 56 -33 103 -60 105 -60 2 0 49 -27 105 -60 56 -33 104 -60 107
                                -60 3 0 16 -9 30 -20 14 -11 30 -17 36 -13 7 4 8 120 4 361 -7 390 -5 384 -70
                                422 -14 8 -113 67 -219 132 -106 64 -211 126 -233 138 -22 12 -58 33 -81 46
                                -31 18 -43 21 -52 12z"
                                        />
                                        <path
                                            d="M6465 8271 c-9 -8 -191 -115 -312 -182 -153 -85 -246 -145 -260 -165
                                -14 -21 -15 -66 -8 -375 6 -273 10 -352 20 -356 8 -2 58 22 112 54 54 33 105
                                62 113 66 8 4 27 15 42 25 15 9 86 51 158 92 71 41 140 83 152 92 l23 17 0
                                368 c0 321 -2 368 -15 371 -9 1 -20 -2 -25 -7z"
                                        />
                                        <path
                                            d="M4854 7336 c-49 -29 -110 -64 -135 -78 -24 -14 -120 -69 -214 -123
                                -93 -54 -213 -123 -265 -153 -52 -30 -147 -85 -210 -122 -63 -37 -223 -129
                                -355 -205 -132 -75 -243 -142 -247 -148 -12 -20 -41 -2 457 -293 93 -54 177
                                -102 186 -106 25 -10 76 19 727 411 326 196 600 361 610 366 194 112 214 128
                                191 149 -13 12 -317 187 -449 259 -30 16 -59 33 -65 37 -31 22 -108 60 -123
                                60 -9 0 -58 -24 -108 -54z"
                                        />
                                        <path
                                            d="M5650 6837 c-19 -13 -147 -91 -283 -173 -136 -82 -260 -157 -275
                                -166 -15 -10 -36 -22 -47 -28 -11 -6 -32 -18 -47 -28 -22 -14 -189 -115 -648
                                -391 -19 -11 -42 -24 -50 -28 -8 -3 -39 -21 -67 -40 l-53 -33 1 -232 c1 -128
                                4 -296 8 -373 l6 -140 24 3 c13 2 43 16 65 30 54 35 77 49 101 62 11 6 32 18
                                47 28 42 26 370 224 398 240 14 7 37 21 52 30 20 13 616 374 783 475 56 34 54
                                21 55 420 0 344 -1 367 -18 367 -9 0 -33 -11 -52 -23z"
                                        />
                                    </g>
                                </svg>
                            </div>
                            <div className="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]" />
                        </div>
                    </main>
                </div>
                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </>
    );
}
