<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- <title>{{ $title ?? config('app.name') }}</title> --}}
    <title>Iraka Voafidy - Ministère de Danse Chrétienne</title>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-slate-950 text-slate-100 antialiased selection:bg-brand-500 selection:text-white" x-data="{
        authModal: false,
        authTab: 'login',
        mobileMenu: false,
        user: null,
        notification: null,
        showNotification(msg) {
            this.notification = msg;
            setTimeout(() => { this.notification = null; }, 4000);
        }
    }">
    <!-- Notification Toast (Livewire Flash style) -->
    <template x-if="notification">
        <div
            class="fixed top-5 right-5 z-50 bg-emerald-600 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center space-x-3 border border-emerald-400 transition transform duration-300">
            <i class="fa-solid fa-circle-check text-xl"></i>
            <span x-text="notification" class="font-medium"></span>
        </div>
    </template>

    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-40 backdrop-blur-md bg-slate-950/80 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-linear-to-tr from-brand-600 to-gold-500 flex items-center justify-center shadow-lg shadow-brand-500/30">
                        <i class="fa-solid fa-hands-praying text-white text-lg"></i>
                    </div>
                    <a href="{{ route('home') }}">
                        <span
                            class="text-xl font-bold bg-linear-to-r from-white via-slate-200 to-gold-400 bg-clip-text text-transparent">Iraka
                            Voafidy</span>
                        <span class="block text-xs text-brand-400 font-medium tracking-wider uppercase">Ministère de
                            Danse</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home')  }}"
                        class="text-sm font-medium text-slate-300 hover:text-gold-400 transition">Accueil</a>
                    <a href="{{ route('choreographies')  }}"
                        class="text-sm font-medium text-slate-300 hover:text-gold-400 transition">Chorégraphies</a>
                    <a href="{{ route('evenements')  }}"
                        class="text-sm font-medium text-slate-300 hover:text-gold-400 transition">Événements</a>
                    <a href="{{ route('priere')  }}"
                        class="text-sm font-medium text-slate-300 hover:text-gold-400 transition">Mur de Prière</a>
                    <a href="{{ route('apropos')  }}"
                        class="text-sm font-medium text-slate-300 hover:text-gold-400 transition">À Propos</a>
                    <a href="{{ route('contact')  }}"
                        class="text-sm font-medium text-slate-300 hover:text-gold-400 transition">Contact</a>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    @auth
                        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">

                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}"
                                    class="text-slate-600 hover:text-indigo-600 transition">
                                    Espace Admin
                                </a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="text-slate-600 hover:text-indigo-600 transition">
                                    Tableau de bord
                                </a>
                            @endif
                        </nav>
                        {{-- Déconnection --}}
                        <div
                            class="flex items-center space-x-3 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-xl">
                            <div
                                class="w-8 h-8 rounded-lg bg-brand-700 flex items-center justify-center font-bold text-white text-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-sm font-medium text-slate-200">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 ml-2">
                                    <i class="fa-solid fa-power-off"></i>
                                </button>
                            </form>
                        </div>

                    @else
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('login') }}"
                                class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition">
                                Connexion
                            </a>
                            <a href="{{ route('register') }}"
                                class="px-4 py-2 text-sm font-semibold text-white bg-linear-to-r from-brand-600 to-brand-500 rounded-xl hover:from-brand-500 hover:to-brand-600 shadow-lg shadow-brand-600/30 transition transform hover:-translate-y-0.5">
                                Inscription
                            </a>
                        </div>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenu = !mobileMenu" class="text-slate-300 hover:text-white p-2">
                        <i class="fa-solid fa-bars text-xl" :class="mobileMenu ? 'fa-xmark' : 'fa-bars'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Dropdown -->
        <div x-show="mobileMenu" x-transition
            class="md:hidden bg-slate-900 border-b border-slate-800 px-4 pt-2 pb-6 space-y-3">
            <a href="{{ route('home')  }}" @click="mobileMenu = false"
                class="block text-slate-300 hover:text-gold-400 py-2">Accueil</a>
            <a href="{{ route('choreographies')  }}" @click="mobileMenu = false"
                class="block text-slate-300 hover:text-gold-400 py-2">Chorégraphies</a>
            <a href="{{ route('evenements')  }}" @click="mobileMenu = false"
                class="block text-slate-300 hover:text-gold-400 py-2">Événements</a>
            <a href="{{ route('priere')  }}" @click="mobileMenu = false"
                class="block text-slate-300 hover:text-gold-400 py-2">Mur de Prière</a>
            <a href="{{ route('apropos')  }}" @click="mobileMenu = false"
                class="block text-slate-300 hover:text-gold-400 py-2">À Propos</a>
            <a href="{{ route('contact')  }}" @click="mobileMenu = false"
                class="block text-slate-300 hover:text-gold-400 py-2">Contact</a>
            <div class="pt-4 border-t border-slate-800 flex flex-col space-y-2">
                <template x-if="!user">
                    <div class="flex flex-col space-y-2">
                        <button @click="authModal = true; authTab = 'login'; mobileMenu = false"
                            class="w-full py-2 text-center text-sm font-semibold text-white bg-slate-800 rounded-xl">Connexion</button>
                        <button @click="authModal = true; authTab = 'register'; mobileMenu = false"
                            class="w-full py-2 text-center text-sm font-semibold text-white bg-brand-600 rounded-xl">Inscription</button>
                    </div>
                </template>
                <template x-if="user">
                    <button @click="user = null; showNotification('Déconnecté'); mobileMenu = false"
                        class="w-full py-2 text-center text-sm text-rose-400 bg-rose-950/30 rounded-xl">Déconnexion</button>
                </template>
            </div>
        </div>
    </nav>

    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800 text-slate-400 text-sm relative overflow-hidden">
        <!-- Background Ambient Glow -->
        <div
            class="absolute bottom-0 left-1/2 -translate-x-1/2 w-3/4 h-32 bg-brand-600/10 blur-3xl pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-12 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 pb-16 border-b border-slate-900">

                <!-- Col 1: About & Mission -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-linear-to-tr from-brand-600 to-gold-500 flex items-center justify-center text-white shadow-lg shadow-brand-600/30">
                            <i class="fa-solid fa-hands-praying text-sm"></i>
                        </div>
                        <div>
                            <span
                                class="text-xl font-bold bg-linear-to-r from-white via-slate-200 to-gold-400 bg-clip-text text-transparent">Iraka
                                Voafidy</span>
                            <span class="block text-xs text-brand-400 font-medium tracking-wider uppercase">Ministère de
                                Danse Chrétienne</span>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed pr-4">
                        <strong class="text-slate-200">Iraka Voafidy</strong> (Les Envoyés Choisis) est un sanctuaire et
                        un ministère dédié à la louange et à l'adoration prophétique par le mouvement corporel. Nous
                        formons des adorateurs passionnés pour élever le nom du Roi des Rois à Madagascar et dans les
                        nations.
                    </p>
                    <div class="p-4 bg-slate-900/60 border border-slate-800 rounded-2xl">
                        <p class="text-xs italic text-gold-400 font-medium">
                            « Louez-le avec des tambourins et des danses ! Louez-le avec des instruments à cordes et des
                            chalumeaux ! »
                        </p>
                        <span class="block text-right text-xs text-slate-500 mt-1">— Psaume 149:3</span>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-base tracking-wide uppercase text-brand-400">Liens Rapides</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('home')  }}" class="hover:text-gold-400 transition flex items-center"><i
                                    class="fa-solid fa-chevron-right text-xs mr-2 text-brand-500"></i> Accueil</a></li>
                        <li><a href="{{ route('choreographies')  }}"
                                class="hover:text-gold-400 transition flex items-center"><i
                                    class="fa-solid fa-chevron-right text-xs mr-2 text-brand-500"></i> Cours de
                                Danse</a></li>
                        <li><a href="{{ route('evenements')  }}"
                                class="hover:text-gold-400 transition flex items-center"><i
                                    class="fa-solid fa-chevron-right text-xs mr-2 text-brand-500"></i> Événements &
                                Spectacles</a></li>
                        <li><a href="#priere" class="hover:text-gold-400 transition flex items-center"><i
                                    class="fa-solid fa-chevron-right text-xs mr-2 text-brand-500"></i> Mur de Prière</a>
                        </li>
                        <li><a href="#apropos" class="hover:text-gold-400 transition flex items-center"><i
                                    class="fa-solid fa-chevron-right text-xs mr-2 text-brand-500"></i> À Propos</a></li>
                        <li><a href="#contact" class="hover:text-gold-400 transition flex items-center"><i
                                    class="fa-solid fa-chevron-right text-xs mr-2 text-brand-500"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Col 3: Dance Categories -->
                <div class="space-y-4">
                    <h4 class="text-white font-bold text-base tracking-wide uppercase text-brand-400">Styles &
                        Catégories</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li class="hover:text-gold-400 transition flex items-center"><i
                                class="fa-solid fa-circle-dot text-[8px] mr-2 text-brand-400"></i> Danse Contemporaine
                            Chrétienne</li>
                        <li class="hover:text-gold-400 transition flex items-center"><i
                                class="fa-solid fa-circle-dot text-[8px] mr-2 text-brand-400"></i> Louange Prophétique &
                            Tambourins</li>
                        <li class="hover:text-gold-400 transition flex items-center"><i
                                class="fa-solid fa-circle-dot text-[8px] mr-2 text-brand-400"></i> Expression Corporelle
                            & Psaumes</li>
                        <li class="hover:text-gold-400 transition flex items-center"><i
                                class="fa-solid fa-circle-dot text-[8px] mr-2 text-brand-400"></i> Ateliers Jeunesse &
                            Enfants</li>
                        <li class="hover:text-gold-400 transition flex items-center"><i
                                class="fa-solid fa-circle-dot text-[8px] mr-2 text-brand-400"></i> Rythmes Traditionnels
                            Sacrés</li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter & Contact Summary -->
                <div class="space-y-4" x-data="{ newsEmail: '', subscribed: false }">
                    <h4 class="text-white font-bold text-base tracking-wide uppercase text-brand-400">Abonnement
                        Spirituel</h4>
                    <p class="text-xs text-slate-400">Recevez nos versets hebdomadaires, annonces d'ateliers et rappels
                        de veillées de prière.</p>

                    <template x-if="subscribed">
                        <div
                            class="p-3 bg-emerald-950/80 border border-emerald-500 text-emerald-300 rounded-xl text-xs text-center">
                            <i class="fa-solid fa-circle-check mr-1"></i> Merci ! Inscription confirmée.
                        </div>
                    </template>

                    <template x-if="!subscribed">
                        <form
                            @submit.prevent="if(newsEmail){ subscribed = true; showNotification('Inscription à la newsletter réussie !'); }"
                            class="space-y-2">
                            <input type="email" x-model="newsEmail" required placeholder="Votre e-mail..."
                                class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-brand-500">
                            <button type="submit"
                                class="w-full py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl text-xs transition shadow-md shadow-brand-600/30">
                                S'abonner
                            </button>
                        </form>
                    </template>

                    <div class="pt-2 text-xs space-y-1 text-slate-400">
                        <p><i class="fa-solid fa-clock mr-1 text-gold-400"></i> Répétitions : Mercredi & Samedi (14h)
                        </p>
                        <p><i class="fa-solid fa-location-dot mr-1 text-gold-400"></i> Ankorondrano, Antananarivo</p>
                    </div>
                </div>

            </div>

            <!-- Social Media & Bottom Bar -->
            <div class="pt-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Social Icons -->
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-slate-400 mr-2 font-medium">Suivez-nous :</span>
                    <a href="https://facebook.com" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-brand-600 hover:border-brand-500 transition shadow"><i
                            class="fa-brands fa-facebook-f text-sm"></i></a>
                    <a href="https://youtube.com" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-brand-600 hover:border-brand-500 transition shadow"><i
                            class="fa-brands fa-youtube text-sm"></i></a>
                    <a href="https://instagram.com" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-brand-600 hover:border-brand-500 transition shadow"><i
                            class="fa-brands fa-instagram text-sm"></i></a>
                    <a href="https://tiktok.com" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-brand-600 hover:border-brand-500 transition shadow"><i
                            class="fa-brands fa-tiktok text-sm"></i></a>
                    <a href="https://whatsapp.com" target="_blank"
                        class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-300 hover:text-white hover:bg-emerald-600 hover:border-emerald-500 transition shadow"><i
                            class="fa-brands fa-whatsapp text-sm"></i></a>
                </div>

                <!-- Legal Links -->
                <div class="flex flex-wrap items-center justify-center gap-6 text-xs text-slate-400">
                    <a href="#" class="hover:text-gold-400 transition">Politique de confidentialité</a>
                    <span class="text-slate-700">•</span>
                    <a href="#" class="hover:text-gold-400 transition">Conditions d'utilisation</a>
                    <span class="text-slate-700">•</span>
                    <a href="#" class="hover:text-gold-400 transition">Mentions légales</a>
                </div>
            </div>

            <!-- Copyright & Tech Badge -->
            <div
                class="flex flex-col sm:flex-row items-center justify-center text-xs text-slate-500 pt-8 mt-8 border-t border-slate-900 gap-4">
                <p>&copy; 2026 Iraka Voafidy. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>

</html>