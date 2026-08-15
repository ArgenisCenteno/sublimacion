<template>
  <Head title="Laboratorio Clínico" />

  <Transition name="fade">
    <div
      v-if="isLoading"
      class="fixed inset-0 z-[999] flex flex-col items-center justify-center gap-4 bg-white dark:bg-gray-900"
    >
      <Heart fill="#42AAAB" class="heartbeat h-16 w-16 text-[#42AAAB]" />
      <p class="text-sm font-medium tracking-wide text-gray-400 dark:text-gray-500">Cargando...</p>
    </div>
  </Transition>

  <FullScreenLayout>
    <div class="bg-white dark:bg-gray-900">
      <!-- Navbar -->
      <header
        class="fixed top-0 z-50 w-full transition-colors duration-300"
        :class="scrolled ? 'bg-white/95 shadow-theme-sm backdrop-blur-md dark:bg-gray-900/95' : 'bg-transparent'"
      >
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3 lg:px-8">
          <a href="#inicio" @click="handleNavClick($event, '#inicio')" class="flex items-center gap-2.5">
            <span
              class="flex h-9 w-9 items-center justify-center rounded-full transition-colors duration-300"
              :class="scrolled ? 'bg-[#42AAAB] text-white' : 'bg-white/15 text-white ring-1 ring-white/30'"
            >
              <HeartPulse class="h-5 w-5" />
            </span>
            <span
              class="text-lg font-semibold transition-colors duration-300"
              :class="scrolled ? 'text-gray-800 dark:text-white' : 'text-white'"
            >
              Biocerra
            </span>
          </a>

          <nav class="hidden items-center gap-8 lg:flex">
            <a
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              @click="handleNavClick($event, link.href)"
              class="text-sm font-medium transition-colors duration-300"
              :class="scrolled ? 'text-gray-600 hover:text-[#358C8D] dark:text-gray-300 dark:hover:text-[#42AAAB]' : 'text-white/85 hover:text-white'"
            >
              {{ link.label }}
            </a>
          </nav>

          <div class="hidden items-center gap-3 lg:flex">
            <Link
              href="/signin"
              class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors duration-300"
              :class="
                scrolled
                  ? 'border-gray-200 text-gray-700 hover:border-[#42AAAB] hover:text-[#358C8D] dark:border-gray-700 dark:text-gray-300 dark:hover:border-[#42AAAB] dark:hover:text-[#42AAAB]'
                  : 'border-white/40 text-white hover:bg-white/10'
              "
            >
              Iniciar sesión
            </Link>
            <a
              :href="whatsappHref"
              target="_blank"
              rel="noopener"
              class="flex items-center gap-2 rounded-lg bg-[#42AAAB] px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition hover:bg-[#358C8D]"
            >
              <WhatsAppIcon class="h-4 w-4" />
              Escríbenos
            </a>
            <ThemeToggler v-if="scrolled" />
            <button
              v-else
              class="relative flex h-11 w-11 items-center justify-center rounded-full text-white ring-1 ring-white/30 transition-colors hover:bg-white/10"
              aria-label="Cambiar tema"
              @click.prevent="toggleTheme"
            >
              <svg class="hidden dark:block" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z" fill="currentColor" />
              </svg>
              <svg class="dark:hidden" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z" fill="currentColor" />
              </svg>
            </button>
          </div>

          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="transition-colors duration-300 lg:hidden"
            :class="scrolled || mobileMenuOpen ? 'text-gray-600 dark:text-gray-300' : 'text-white'"
            aria-label="Abrir menú"
          >
            <X v-if="mobileMenuOpen" class="h-6 w-6" />
            <Menu v-else class="h-6 w-6" />
          </button>
        </div>

        <!-- Mobile menu -->
        <Transition name="slide-down">
          <div v-if="mobileMenuOpen" class="border-t border-gray-100 bg-white px-6 py-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:hidden">
            <div class="flex flex-col gap-4">
              <a
                v-for="link in navLinks"
                :key="link.href"
                :href="link.href"
                @click="handleNavClick($event, link.href)"
                class="text-sm font-medium text-gray-600 dark:text-gray-300"
              >
                {{ link.label }}
              </a>
              <Link href="/signin" class="text-sm font-medium text-[#358C8D] dark:text-[#42AAAB]">Iniciar sesión</Link>
              <a
                :href="whatsappHref"
                target="_blank"
                rel="noopener"
                class="flex items-center justify-center gap-2 rounded-lg bg-[#42AAAB] px-4 py-2.5 text-sm font-medium text-white"
              >
                <WhatsAppIcon class="h-4 w-4" />
                Escríbenos por WhatsApp
              </a>
              <button
                type="button"
                class="flex items-center justify-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:text-gray-300"
                @click="toggleTheme"
              >
                <Sun class="h-4 w-4 dark:hidden" />
                <Moon class="hidden h-4 w-4 dark:block" />
                <span class="dark:hidden">Modo oscuro</span>
                <span class="hidden dark:inline">Modo claro</span>
              </button>
            </div>
          </div>
        </Transition>
      </header>

      <!-- Hero -->
      <section id="inicio" class="relative scroll-mt-24 overflow-hidden bg-gradient-to-br from-[#42AAAB] to-[#276667]">
        <div class="absolute right-0 top-0 -z-10 w-full max-w-[250px] opacity-40 xl:max-w-[450px]">
          <img src="/images/shape/grid-01.svg" alt="" />
        </div>
        <div class="absolute bottom-0 left-0 -z-10 w-full max-w-[250px] rotate-180 opacity-40 xl:max-w-[450px]">
          <img src="/images/shape/grid-01.svg" alt="" />
        </div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 pb-20 pt-32 lg:grid-cols-2 lg:px-8 lg:pb-28 lg:pt-40">
          <div>
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium text-white ring-1 ring-white/25">
              <Microscope class="h-4 w-4" />
              Laboratorio Clínico
            </span>

            <div class="relative mt-6">
              <h1 aria-hidden="true" class="invisible text-title-sm font-semibold sm:text-title-md lg:text-title-lg">
                {{ heroHeading }}
              </h1>
              <h1 class="absolute inset-0 text-title-sm font-semibold text-white sm:text-title-md lg:text-title-lg">
                {{ typedHeading }}<span class="typewriter-cursor">|</span>
              </h1>
            </div>

            <p class="mt-5 max-w-lg text-base text-white/80 sm:text-lg">
              En Biocerra realizamos tus análisis clínicos con tecnología moderna, personal certificado y atención
              cercana, para darte resultados precisos y a tiempo.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-4">
              <a
                :href="whatsappHref"
                target="_blank"
                rel="noopener"
                class="flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-[#276667] shadow-theme-md transition hover:bg-white/90"
              >
                <WhatsAppIcon class="h-5 w-5" />
                Agenda por WhatsApp
              </a>
              <a
                href="#servicios"
                @click="handleNavClick($event, '#servicios')"
                class="flex items-center gap-2 rounded-lg border border-white/40 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
              >
                Ver servicios
                <ArrowRight class="h-4 w-4" />
              </a>
            </div>

            <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3">
              <div class="flex items-center gap-2 text-sm text-white/80">
                <ShieldCheck class="h-5 w-5 text-white" />
                Personal certificado
              </div>
              <div class="flex items-center gap-2 text-sm text-white/80">
                <Clock class="h-5 w-5 text-white" />
                Atención rápida
              </div>
              <div class="flex items-center gap-2 text-sm text-white/80">
                <Lock class="h-5 w-5 text-white" />
                Confidencialidad
              </div>
            </div>
          </div>

          <!-- Hero visual -->
          <div class="relative hidden h-[460px] lg:block">
            <div class="absolute inset-x-6 inset-y-6 overflow-hidden rounded-[2rem] bg-gradient-to-br from-white/15 to-white/0 ring-1 ring-white/20 backdrop-blur-sm">
              <div class="flex h-full items-center justify-center">
                <img src="landing/hero.png" alt="hola">
              </div>
            </div>

            <div class="float-slow absolute left-2 top-4 w-56 rounded-2xl bg-white p-5 shadow-theme-xl dark:bg-gray-800">
              <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#42AAAB]/10 dark:bg-[#42AAAB]/20">
                  <TestTube class="h-5 w-5 text-[#358C8D] dark:text-[#42AAAB]" />
                </span>
                <div>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Química Sanguínea</p>
                  <p class="text-xs text-gray-400">Perfil completo</p>
                </div>
              </div>
            </div>

            <div class="float-slow-delayed absolute right-0 top-32 w-60 rounded-2xl bg-white p-5 shadow-theme-xl dark:bg-gray-800">
              <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/15">
                  <CircleCheck class="h-5 w-5 text-success-600 dark:text-success-500" />
                </span>
                <div>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Resultado listo</p>
                  <p class="text-xs text-gray-400">Disponible en línea</p>
                </div>
              </div>
            </div>

            <div class="float-slow absolute bottom-6 left-12 w-52 rounded-2xl bg-white p-5 shadow-theme-xl dark:bg-gray-800">
              <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#42AAAB]/10 dark:bg-[#42AAAB]/20">
                  <HeartPulseIcon class="h-5 w-5 text-[#358C8D] dark:text-[#42AAAB]" />
                </span>
                <div>
                  <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Perfil Lipídico</p>
                  <p class="text-xs text-gray-400">Salud cardiovascular</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <svg class="relative block w-full text-white dark:text-gray-900" viewBox="0 0 1440 60" fill="currentColor" preserveAspectRatio="none">
          <path d="M0,32 C240,60 480,60 720,40 C960,20 1200,0 1440,20 L1440,60 L0,60 Z" />
        </svg>
      </section>

      <!-- Servicios -->
      <section id="servicios" class="mx-auto max-w-7xl scroll-mt-24 px-6 py-20 lg:px-8">
        <div v-reveal class="reveal mx-auto max-w-2xl text-center">
          <h2 class="text-title-sm font-semibold text-gray-800 dark:text-white">Nuestros servicios</h2>
          <p class="mt-3 text-gray-500 dark:text-gray-400">
            Contamos con un amplio panel de exámenes de laboratorio clínico para cuidar tu salud y la de tu familia.
          </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="(servicio, index) in servicios"
            :key="servicio.titulo"
            v-reveal
            :style="{ transitionDelay: `${index * 60}ms` }"
            class="reveal group rounded-2xl border border-gray-100 p-6 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-md dark:border-gray-800 dark:hover:shadow-none"
          >
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#42AAAB]/10 text-[#358C8D] transition group-hover:bg-[#42AAAB] group-hover:text-white dark:bg-[#42AAAB]/15 dark:text-[#42AAAB]">
              <component :is="servicio.icono" class="h-6 w-6" />
            </span>
            <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-gray-100">{{ servicio.titulo }}</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ servicio.descripcion }}</p>
          </div>
        </div>
      </section>

      <!-- Cómo funciona -->
      <section class="bg-gray-50 py-20 dark:bg-white/2">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div v-reveal class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-title-sm font-semibold text-gray-800 dark:text-white">¿Cómo funciona?</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">Realizar tus análisis con nosotros es muy sencillo.</p>
          </div>

          <div class="mt-12 grid gap-8 sm:grid-cols-3">
            <div
              v-for="(paso, index) in pasos"
              :key="paso.titulo"
              v-reveal
              :style="{ transitionDelay: `${index * 100}ms` }"
              class="reveal text-center"
            >
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#42AAAB] text-lg font-semibold text-white shadow-theme-md">
                {{ index + 1 }}
              </div>
              <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-gray-100">{{ paso.titulo }}</h3>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ paso.descripcion }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Quiénes somos -->
      <section id="nosotros" class="relative scroll-mt-24 overflow-hidden py-20">
        <div class="pointer-events-none absolute -left-24 top-10 -z-10 h-72 w-72 rounded-full bg-[#42AAAB]/20 blur-3xl dark:bg-[#42AAAB]/10"></div>
        <div class="pointer-events-none absolute -right-24 bottom-10 -z-10 h-80 w-80 rounded-full bg-blue-light-300/30 blur-3xl dark:bg-blue-light-500/10"></div>

        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div class="grid items-center gap-12 lg:grid-cols-2">
            <div v-reveal class="reveal relative">
              <div class="aspect-[4/3] overflow-hidden rounded-3xl ">
                <div class="flex h-full items-center justify-center">
                <img src="landing/lab.jpg" alt="">
                </div>
              </div>
              <div class="absolute -bottom-6 -right-6 hidden max-w-[220px] rounded-2xl bg-[#42AAAB] p-4 shadow-theme-xl sm:block">
                <div class="flex items-center gap-3">
                  <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15">
                    <Award class="h-5 w-5 text-white" />
                  </span>
                  <p class="text-sm font-medium text-white">Compromiso con la calidad diagnóstica</p>
                </div>
              </div>
            </div>

            <div v-reveal class="reveal">
              <span class="inline-flex items-center gap-2 rounded-full bg-[#42AAAB]/10 px-4 py-1.5 text-sm font-medium text-[#358C8D] dark:bg-[#42AAAB]/15 dark:text-[#42AAAB]">
                <Users class="h-4 w-4" />
                Quiénes somos
              </span>
              <h2 class="mt-5 text-title-sm font-semibold text-gray-800 dark:text-white">
                Comprometidos con tu salud, en cada paso
              </h2>
              <p class="mt-4 text-gray-500 dark:text-gray-400">
                Somos un laboratorio clínico enfocado en ofrecer diagnósticos confiables. Combinamos tecnología
                moderna con un equipo humano capacitado para acompañarte en el cuidado de tu salud, con calidez y
                profesionalismo en cada visita.
              </p>
              <ul class="mt-6 space-y-3">
                <li v-for="item in nosotrosPuntos" :key="item" class="flex items-start gap-3 text-sm text-gray-600 dark:text-gray-300">
                  <CircleCheck class="mt-0.5 h-5 w-5 shrink-0 text-[#358C8D] dark:text-[#42AAAB]" />
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>

          <div
            v-reveal
            class="reveal relative mt-20 overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#42AAAB]/8 via-white to-blue-light-50/60 px-6 py-16 ring-1 ring-gray-900/5 dark:from-[#42AAAB]/10 dark:via-gray-900 dark:to-gray-800 dark:ring-white/10 sm:px-10"
          >
            <div class="pointer-events-none absolute -left-16 -top-16 -z-10 h-64 w-64 rounded-full bg-[#42AAAB]/20 blur-3xl dark:bg-[#42AAAB]/10"></div>
            <div class="pointer-events-none absolute -right-16 -bottom-16 -z-10 h-72 w-72 rounded-full bg-blue-light-300/25 blur-3xl dark:bg-blue-light-500/10"></div>

            <div class="mx-auto max-w-2xl text-center">
              <span class="inline-flex items-center gap-2 rounded-full bg-[#42AAAB]/10 px-4 py-1.5 text-sm font-medium text-[#358C8D] dark:bg-[#42AAAB]/15 dark:text-[#42AAAB]">
                <Award class="h-4 w-4" />
                Nuestros diferenciales
              </span>
              <h3 class="mt-5 text-title-sm font-semibold text-gray-800 dark:text-white">Por qué elegir Biocerra</h3>
              <p class="mt-3 text-gray-500 dark:text-gray-400">
                Cuatro razones por las que pacientes y médicos confían en nuestro laboratorio para su diagnóstico.
              </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <div
                v-for="(valor, index) in valores"
                :key="valor.titulo"
                v-reveal
                :style="{ transitionDelay: `${index * 80}ms` }"
                class="reveal group relative flex flex-col items-center rounded-2xl bg-white/80 p-7 text-center shadow-theme-xs ring-1 ring-gray-900/5 backdrop-blur-sm transition duration-300 hover:-translate-y-1.5 hover:shadow-theme-lg dark:bg-white/3 dark:ring-white/10"
              >
                <span class="absolute -top-3 right-6 flex h-7 w-7 items-center justify-center rounded-full bg-white text-xs font-bold text-gray-400 shadow-theme-xs ring-1 ring-gray-100 dark:bg-gray-800 dark:text-gray-500 dark:ring-gray-700">
                  {{ String(index + 1).padStart(2, '0') }}
                </span>
                <span
                  class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br text-white shadow-theme-md transition duration-300 group-hover:scale-110"
                  :class="valor.accent"
                >
                  <component :is="valor.icono" class="h-6 w-6" />
                </span>
                <h4 class="mt-5 text-sm font-semibold text-gray-800 dark:text-gray-100">{{ valor.titulo }}</h4>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ valor.descripcion }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Galería -->
      <section id="galeria" class="relative scroll-mt-24 overflow-hidden bg-gray-50 py-20 dark:bg-white/2">
        <div class="pointer-events-none absolute -left-20 bottom-0 -z-10 h-72 w-72 rounded-full bg-[#42AAAB]/15 blur-3xl dark:bg-[#42AAAB]/10"></div>
        <div class="pointer-events-none absolute -right-20 top-0 -z-10 h-72 w-72 rounded-full bg-blue-light-300/25 blur-3xl dark:bg-blue-light-500/10"></div>

        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div v-reveal class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-title-sm font-semibold text-gray-800 dark:text-white">Galería</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">Conoce un poco de nuestras instalaciones y equipo de trabajo.</p>
          </div>

          <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
              v-for="(item, index) in galeria"
              :key="item.titulo"
              v-reveal
              :style="{ transitionDelay: `${index * 70}ms` }"
              class="reveal group relative aspect-[4/3] overflow-hidden rounded-2xl shadow-theme-xs"
            >
              <img :src="item.imagen" :alt="item.titulo" class="absolute inset-0 h-full w-full object-cover transition duration-300 group-hover:scale-105" />
              <div class="absolute inset-x-0 bottom-0 bg-black/30 px-4 py-2.5 backdrop-blur-sm">
                <p class="text-sm font-medium text-white">{{ item.titulo }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Salud y prevención -->
      <section class="relative bg-white py-16 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div v-reveal class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-title-sm font-semibold text-gray-800 dark:text-white">Cuida tu salud</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">Pequeños hábitos de prevención que marcan una gran diferencia.</p>
          </div>

          <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
              v-for="(tip, index) in saludTips"
              :key="tip.titulo"
              v-reveal
              :style="{ transitionDelay: `${index * 70}ms` }"
              class="reveal flex items-start gap-4 rounded-2xl border border-gray-100 p-5 shadow-theme-xs dark:border-gray-800"
            >
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" :class="tip.color">
                <component :is="tip.icono" class="h-5 w-5" />
              </span>
              <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ tip.titulo }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ tip.texto }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

     

      <!-- FAQ -->
      <section id="faq" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-white/2">
        <div class="mx-auto max-w-3xl px-6 lg:px-8">
          <div v-reveal class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-title-sm font-semibold text-gray-800 dark:text-white">Preguntas frecuentes</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">Resolvemos las dudas más comunes sobre nuestros servicios de laboratorio.</p>
          </div>

          <div class="mt-10 space-y-3">
            <div
              v-for="(faq, index) in faqs"
              :key="faq.pregunta"
              v-reveal
              :style="{ transitionDelay: `${index * 60}ms` }"
              class="reveal overflow-hidden rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900"
            >
              <button
                type="button"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                @click="toggleFaq(index)"
              >
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ faq.pregunta }}</span>
                <ChevronDown
                  class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200 dark:text-gray-500"
                  :class="{ 'rotate-180': openFaq === index }"
                />
              </button>
              <div v-show="openFaq === index" class="px-5 pb-4 text-sm text-gray-500 dark:text-gray-400">
                {{ faq.respuesta }}
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Contacto e Ubicación -->
      <section id="contacto" class="relative scroll-mt-24 overflow-hidden bg-gradient-to-br from-[#1f4f50] to-[#123536] py-24">
        <div class="absolute right-0 top-0 -z-10 w-full max-w-[250px] opacity-20 xl:max-w-[450px]">
          <img src="/images/shape/grid-01.svg" alt="" />
        </div>

        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div v-reveal class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-title-sm font-semibold text-white">Hablemos</h2>
            <p class="mt-3 text-white/70">Estamos para ayudarte con tus dudas, resultados o para agendar tu visita.</p>
          </div>

          <div class="mt-14 grid gap-8 lg:grid-cols-2">
            <div v-reveal class="reveal rounded-3xl bg-white/10 p-8 ring-1 ring-white/10 backdrop-blur-sm">
              <h3 class="text-lg font-semibold text-white">Envíanos un mensaje</h3>
              <p class="mt-2 text-sm text-white/70">Completa el formulario y te contactaremos a la brevedad.</p>

              <form class="mt-6 space-y-4" @submit.prevent="handleContactSubmit">
                <input
                  v-model="contactForm.nombre"
                  type="text"
                  name="nombre"
                  placeholder="Nombre completo"
                  required
                  class="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/50 focus:border-white/40 focus:outline-none"
                />
                <div class="grid gap-4 sm:grid-cols-2">
                  <input
                    v-model="contactForm.correo"
                    type="email"
                    name="correo"
                    placeholder="Correo electrónico"
                    required
                    class="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/50 focus:border-white/40 focus:outline-none"
                  />
                  <input
                    v-model="contactForm.telefono"
                    type="tel"
                    name="telefono"
                    placeholder="Teléfono"
                    class="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/50 focus:border-white/40 focus:outline-none"
                  />
                </div>
                <textarea
                  v-model="contactForm.mensaje"
                  name="mensaje"
                  rows="4"
                  placeholder="¿En qué podemos ayudarte?"
                  required
                  class="w-full resize-none rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-white/50 focus:border-white/40 focus:outline-none"
                ></textarea>
                <button
                  type="submit"
                  class="flex w-full items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-[#276667] shadow-theme-md transition hover:bg-white/90"
                >
                  Enviar mensaje
                  <ArrowRight class="h-4 w-4" />
                </button>
              </form>
            </div>

            <div class="flex flex-col gap-8">
              <div v-reveal class="reveal rounded-3xl bg-white/10 p-8 ring-1 ring-white/10 backdrop-blur-sm">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[#25D366]/20">
                  <WhatsAppIcon class="h-6 w-6 text-[#25D366]" />
                </span>
                <h3 class="mt-5 text-lg font-semibold text-white">Escríbenos</h3>
                <p class="mt-2 text-sm text-white/70">
                  Respondemos rápido por WhatsApp para resolver tus dudas o agendar tu examen.
                </p>
                <a
                  :href="whatsappHref"
                  target="_blank"
                  rel="noopener"
                  class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-[#276667] shadow-theme-md transition hover:bg-white/90"
                >
                  <WhatsAppIcon class="h-5 w-5" />
                  {{ whatsappDisplay }}
                </a>
              </div>

              <div v-reveal class="reveal rounded-3xl bg-white/10 p-8 ring-1 ring-white/10 backdrop-blur-sm">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15">
                  <Mail class="h-6 w-6 text-white" />
                </span>
                <h3 class="mt-5 text-lg font-semibold text-white">Escríbenos por correo</h3>
                <p class="mt-2 text-sm text-white/70">
                  Envíanos tus dudas o solicitudes y te responderemos a la brevedad.
                </p>
                <a
                  href="mailto:contacto@biocerra.com"
                  class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-[#276667] shadow-theme-md transition hover:bg-white/90"
                >
                  <Mail class="h-5 w-5" />
                  contacto@biocerra.com
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Ubicación -->
      <section id="ubicacion" class="scroll-mt-24 bg-gray-50 py-20 dark:bg-white/2">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div v-reveal class="reveal mx-auto max-w-2xl text-center">
            <h2 class="text-title-sm font-semibold text-gray-800 dark:text-white">Ubicación</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">{{ direccionLaboratorio }}</p>
          </div>

          <div v-reveal class="reveal mt-10 overflow-hidden rounded-3xl shadow-theme-xs">
            <iframe
              :src="mapsEmbedSrc"
              class="h-80 w-full border-0 sm:h-96"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>

          <div class="mt-6 text-center">
            <a
              :href="mapsHref"
              target="_blank"
              rel="noopener"
              class="inline-flex items-center gap-2 rounded-lg bg-[#42AAAB] px-5 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-[#358C8D]"
            >
              <Navigation class="h-4 w-4" />
              Ver ubicación
            </a>
          </div>
        </div>
      </section>

       <!-- Prevención -->
      <section class="relative overflow-hidden bg-gradient-to-r from-[#42AAAB] via-[#2f8f90] to-[#1f4f50] py-14">
        <div class="mx-auto max-w-3xl px-6 text-center lg:px-8">
          <span v-reveal class="reveal inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium text-white">
            <ShieldCheck class="h-4 w-4" />
            Prevención
          </span>
          <h2 v-reveal class="reveal mt-5 text-title-sm font-semibold text-white">Más vale prevenir que curar</h2>
          <p v-reveal class="reveal mt-3 text-white/80">
            Un chequeo a tiempo puede marcar la diferencia. Agenda tus exámenes de rutina y cuida tu salud antes de
            que aparezcan síntomas.
          </p>
          <a
            v-reveal
            :href="whatsappHref"
            target="_blank"
            rel="noopener"
            class="reveal mt-7 inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-[#276667] shadow-theme-md transition hover:bg-white/90"
          >
            Agenda tu chequeo
            <ArrowRight class="h-4 w-4" />
          </a>
        </div>
      </section>
      <!-- Footer -->
      <footer class="bg-gray-900 py-16">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
          <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#42AAAB] text-white">
                  <HeartPulse class="h-5 w-5" />
                </span>
                <span class="text-lg font-semibold text-white">Biocerra</span>
              </div>
              <p class="mt-4 max-w-xs text-sm text-gray-400">
                Laboratorio clínico comprometido con resultados confiables y una atención cercana.
              </p>
            </div>

            <div>
              <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-300">Enlaces</h4>
              <ul class="mt-4 space-y-2.5">
                <li v-for="link in navLinks" :key="link.href">
                  <a
                    :href="link.href"
                    @click="handleNavClick($event, link.href)"
                    class="text-sm text-gray-400 transition hover:text-white"
                  >
                    {{ link.label }}
                  </a>
                </li>
              </ul>
            </div>

            <div>
              <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-300">Servicios</h4>
              <ul class="mt-4 space-y-2.5">
                <li v-for="servicio in servicios.slice(0, 5)" :key="servicio.titulo" class="text-sm text-gray-400">
                  {{ servicio.titulo }}
                </li>
              </ul>
            </div>

            <div>
              <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-300">Contacto</h4>
              <ul class="mt-4 space-y-3">
                <li>
                  <a :href="whatsappHref" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm text-gray-400 transition hover:text-white">
                    <WhatsAppIcon class="h-4 w-4 shrink-0" />
                    {{ whatsappDisplay }}
                  </a>
                </li>
                <li class="flex items-start gap-2 text-sm text-gray-400">
                  <MapPin class="mt-0.5 h-4 w-4 shrink-0" />
                  {{ direccionLaboratorio }}
                </li>
              </ul>
            </div>
          </div>

          <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-8 sm:flex-row">
            <p class="text-xs text-gray-500">&copy; {{ year }} Biocerra. Todos los derechos reservados.</p>
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
              <Link href="/privacidad" class="text-xs text-gray-500 transition hover:text-white">Privacidad</Link>
              <Link href="/cookies" class="text-xs text-gray-500 transition hover:text-white">Cookies</Link>
              <Link href="/terminos" class="text-xs text-gray-500 transition hover:text-white">Términos</Link>
              <Link href="/signin" class="text-xs text-gray-500 transition hover:text-white">Acceso al sistema</Link>
            </div>
          </div>
        </div>
      </footer>
    </div>

    <!-- Floating WhatsApp button -->
    <a
      :href="whatsappHref"
      target="_blank"
      rel="noopener"
      aria-label="Escríbenos por WhatsApp"
      class="fixed right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-theme-xl transition hover:scale-105"
      :class="showCookieBanner ? 'bottom-28' : 'bottom-6'"
    >
      <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#25D366] opacity-40"></span>
      <WhatsAppIcon class="relative h-7 w-7" />
    </a>

    <!-- Aviso de cookies -->
    <Transition name="fade">
      <div
        v-if="showCookieBanner"
        class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white/95 px-6 py-4 shadow-theme-lg backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900/95"
      >
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row">
          <p class="text-sm text-gray-600 dark:text-gray-300">
            Usamos cookies para mejorar tu experiencia. Al continuar navegando aceptas nuestra
            <Link href="/cookies" class="font-medium text-[#358C8D] underline dark:text-[#42AAAB]">Política de Cookies</Link>.
          </p>
          <div class="flex shrink-0 items-center gap-3">
            <button
              type="button"
              class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-600"
              @click="rejectCookies"
            >
              Rechazar
            </button>
            <button
              type="button"
              class="rounded-lg bg-[#42AAAB] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#358C8D]"
              @click="acceptCookies"
            >
              Aceptar
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref, computed, h, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import ThemeToggler from '@/components/common/ThemeToggler.vue'
import { useTheme } from '@/components/layout/ThemeProvider.vue'
import {
  Menu,
  X,
  Microscope,
  ArrowRight,
  ShieldCheck,
  Clock,
  Lock,
  TestTube,
  CircleCheck,
  HeartPulse,
  Heart,
  Droplet,
  FlaskConical,
  Syringe,
  ClipboardCheck,
  Dna,
  Award,
  Users,
  MapPin,
  Navigation,
  Stethoscope,
  Mail,
  ChevronDown,
  Sun,
  Moon,
} from 'lucide-vue-next'

const { toggleTheme } = useTheme()

// Íconos alias para evitar choques de nombre en el template
const HeartPulseIcon = HeartPulse

// --- Loading (heartbeat) ---
const isLoading = ref(true)

// --- Navbar que se adapta al hacer scroll ---
const scrolled = ref(false)
const mobileMenuOpen = ref(false)
const onScroll = () => {
  scrolled.value = window.scrollY > 40
}

// --- Animación de escritura del titular ---
const heroHeading = 'Cuidamos tu salud con resultados en los que puedes confiar'
const typedHeading = ref('')
let typingTimeout: ReturnType<typeof setTimeout> | undefined

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()

  setTimeout(() => {
    isLoading.value = false
  }, 1100)

  const startTyping = () => {
    let i = 0
    const type = () => {
      typedHeading.value = heroHeading.slice(0, i)
      if (i < heroHeading.length) {
        i++
        typingTimeout = setTimeout(type, 32)
      }
    }
    type()
  }
  setTimeout(startTyping, 1300)
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  if (typingTimeout) clearTimeout(typingTimeout)
})

// --- Aviso de cookies ---
const cookieConsentKey = 'biocerra_cookie_consent'
const showCookieBanner = ref(false)

onMounted(() => {
  if (!localStorage.getItem(cookieConsentKey)) {
    showCookieBanner.value = true
  }
})

const acceptCookies = () => {
  localStorage.setItem(cookieConsentKey, 'accepted')
  showCookieBanner.value = false
}

const rejectCookies = () => {
  localStorage.setItem(cookieConsentKey, 'rejected')
  showCookieBanner.value = false
}

// --- FAQ ---
const openFaq = ref<number | null>(null)
const toggleFaq = (index: number) => {
  openFaq.value = openFaq.value === index ? null : index
}

const faqs = [
  {
    pregunta: '¿Necesito estar en ayunas para mis exámenes?',
    respuesta:
      'La mayoría de los exámenes de química sanguínea requieren entre 8 y 12 horas de ayuno. Escríbenos por WhatsApp para confirmar la preparación específica de tu estudio.',
  },
  {
    pregunta: '¿Necesito una orden médica?',
    respuesta:
      'Para la mayoría de los estudios sí, pero también realizamos pruebas rápidas y de rutina sin orden médica. Contáctanos para verificar tu caso.',
  },
  {
    pregunta: '¿Cuánto tiempo tardan mis resultados?',
    respuesta:
      'Depende del tipo de examen: los estudios de rutina suelen estar listos en 24 a 48 horas, mientras que algunos estudios especializados pueden tardar un poco más.',
  },
  {
    pregunta: '¿Cómo recibo mis resultados?',
    respuesta: 'Puedes recibirlos por correo electrónico o WhatsApp, o retirarlos directamente en nuestras instalaciones.',
  },
  {
    pregunta: '¿Debo agendar una cita previa?',
    respuesta:
      'No es obligatorio, pero te recomendamos escribirnos por WhatsApp antes de tu visita para evitar tiempos de espera.',
  },
  {
    pregunta: '¿Mis resultados son confidenciales?',
    respuesta:
      'Sí. Tus resultados son estrictamente confidenciales y solo tú y el médico que solicitó el estudio pueden acceder a ellos.',
  },
]

// --- Scroll suave al navegar por anclas internas ---
const handleNavClick = (event: MouseEvent, href: string) => {
  if (!href.startsWith('#')) return
  event.preventDefault()
  document.querySelector(href)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  mobileMenuOpen.value = false
}

// --- Directiva local: animación al entrar en pantalla ---
const vReveal = {
  mounted(el: HTMLElement) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            el.classList.add('reveal-visible')
            observer.unobserve(el)
          }
        })
      },
      { threshold: 0.15 }
    )
    observer.observe(el)
  },
}

const year = new Date().getFullYear()

const whatsappNumber = '584128558284'
const whatsappMessage = 'Hola, quisiera información sobre los servicios del laboratorio.'
const whatsappHref = computed(
  () => `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(whatsappMessage)}`
)
const whatsappDisplay = '0412-855-8284'

const contactForm = ref({ nombre: '', correo: '', telefono: '', mensaje: '' })
const handleContactSubmit = () => {
  // Pendiente: conectar con el backend de envío del formulario.
}

const direccionLaboratorio = 'Av. Principal, Sector Centro, Ciudad, Venezuela'
const mapsHref = computed(
  () => `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(direccionLaboratorio)}`
)
const mapsEmbedSrc = computed(
  () => `https://www.google.com/maps?q=${encodeURIComponent(direccionLaboratorio)}&output=embed`
)

const navLinks = [
  { href: '#inicio', label: 'Inicio' },
  { href: '#servicios', label: 'Servicios' },
  { href: '#nosotros', label: 'Nosotros' },
  { href: '#galeria', label: 'Galería' },
  { href: '#faq', label: 'FAQ' },
  { href: '#contacto', label: 'Contacto' },
  { href: '#ubicacion', label: 'Ubicación' }

]

const WhatsAppIcon = (props: { class?: string }) =>
  h(
    'svg',
    { class: props.class, viewBox: '0 0 24 24', fill: 'currentColor', xmlns: 'http://www.w3.org/2000/svg' },
    [
      h('path', {
        d: 'M12.04 2c-5.52 0-10 4.48-10 10 0 1.77.46 3.45 1.26 4.9L2 22l5.25-1.28A9.96 9.96 0 0 0 12.04 22c5.52 0 10-4.48 10-10s-4.48-10-10-10Zm0 18.2c-1.6 0-3.1-.43-4.4-1.2l-.32-.19-3.11.76.76-3.03-.2-.31A8.17 8.17 0 0 1 3.84 12c0-4.53 3.68-8.2 8.2-8.2 4.53 0 8.2 3.67 8.2 8.2 0 4.52-3.67 8.2-8.2 8.2Zm4.48-6.14c-.24-.12-1.44-.71-1.67-.79-.22-.08-.38-.12-.55.12-.16.24-.63.79-.77.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.32-.75-1.81-.2-.48-.4-.41-.55-.42-.14-.01-.3-.01-.46-.01a.9.9 0 0 0-.65.3c-.22.24-.85.83-.85 2.03 0 1.2.87 2.36.99 2.52.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z',
      }),
    ]
  )

const servicios = [
  { titulo: 'Hematología', descripcion: 'Hematología completa, tipiaje y coagulación.', icono: Droplet },
  { titulo: 'Química Sanguínea', descripcion: 'Glicemia, urea, creatinina y perfiles metabólicos.', icono: TestTube },
  { titulo: 'Perfil Lipídico', descripcion: 'Colesterol, triglicéridos y salud cardiovascular.', icono: HeartPulse },
  { titulo: 'Uroanálisis', descripcion: 'Examen de orina completo y sedimento urinario.', icono: FlaskConical },
  { titulo: 'Perfil Hormonal', descripcion: 'Tiroides, fertilidad y evaluación endocrina.', icono: Dna },
  { titulo: 'Microbiología', descripcion: 'Cultivos, antibiogramas y estudios bacteriológicos.', icono: Microscope },
  { titulo: 'Serología', descripcion: 'Pruebas inmunológicas y de enfermedades infecciosas.', icono: Syringe },
  { titulo: 'Pruebas Rápidas', descripcion: 'Embarazo, dengue y pruebas de diagnóstico rápido.', icono: ClipboardCheck },
]

const pasos = [
  { titulo: 'Contáctanos', descripcion: 'Escríbenos por WhatsApp para agendar tu cita o resolver dudas.' },
  { titulo: 'Toma de muestra', descripcion: 'Acude al laboratorio y realiza tu toma de muestra sin esperas.' },
  { titulo: 'Recibe tus resultados', descripcion: 'Obtén tus resultados de forma rápida y confiable.' },
]

const nosotrosPuntos = [
  'Bioanalistas y personal certificado',
  'Procesos y equipos validados',
  'Atención cercana y humana en cada visita',
]

const valores = [
  { titulo: 'Personal certificado', descripcion: 'Bioanalistas y personal capacitado.', icono: ShieldCheck, accent: 'from-[#42AAAB] to-[#276667]' },
  { titulo: 'Tecnología moderna', descripcion: 'Equipos actualizados para mayor precisión.', icono: Award, accent: 'from-blue-light-400 to-blue-light-600' },
  { titulo: 'Atención rápida', descripcion: 'Procesos ágiles y resultados a tiempo.', icono: Clock, accent: 'from-warning-400 to-warning-600' },
  { titulo: 'Confidencialidad', descripcion: 'Tu información y resultados están protegidos.', icono: Lock, accent: 'from-success-400 to-success-600' },
]

const galeria = [
  { titulo: 'Recepción', imagen: 'landing/recepcion.jpg' },
  { titulo: 'Toma de muestras', imagen: 'landing/muestras.jpg' },
  { titulo: 'Laboratorio', imagen: 'landing/labo.jpg' },
  { titulo: 'Equipos modernos', imagen: 'landing/equipo.jpg' },
  { titulo: 'Nuestro equipo', imagen: 'landing/empleado.jpg' },
  { titulo: 'Instalaciones', imagen: 'landing/sede.jpg' },
]

const saludTips = [
  {
    titulo: 'Prevención de la diabetes',
    texto: 'Un chequeo de glicemia una vez al año ayuda a detectarla a tiempo.',
    icono: Droplet,
    color: 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
  },
  {
    titulo: 'Salud cardiovascular',
    texto: 'Revisa tu perfil lipídico y tu presión arterial de forma regular.',
    icono: HeartPulse,
    color: 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500',
  },
  {
    titulo: 'Chequeos preventivos',
    texto: 'La prevención es la mejor cura: hazte un chequeo general cada año.',
    icono: Stethoscope,
    color: 'bg-[#42AAAB]/10 text-[#358C8D] dark:bg-[#42AAAB]/15 dark:text-[#42AAAB]',
  },
  {
    titulo: 'Antecedentes y genética',
    texto: 'Conoce tu historial familiar con nuestras pruebas hormonales.',
    icono: Dna,
    color: 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-400',
  },
]
</script>

<style scoped>
@keyframes float-slow {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}
.float-slow {
  animation: float-slow 5s ease-in-out infinite;
}
.float-slow-delayed {
  animation: float-slow 5s ease-in-out infinite;
  animation-delay: 1.5s;
}

@keyframes heartbeat {
  0%, 100% { transform: scale(1); }
  15% { transform: scale(1.3); }
  30% { transform: scale(1); }
  45% { transform: scale(1.2); }
  60% { transform: scale(1); }
}
.heartbeat {
  animation: heartbeat 1.1s ease-in-out infinite;
}

.typewriter-cursor {
  animation: blink 0.9s step-end infinite;
}
@keyframes blink {
  50% { opacity: 0; }
}

.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}
.reveal-visible {
  opacity: 1;
  transform: translateY(0);
}

.dot-grid {
  background-image: radial-gradient(rgba(255, 255, 255, 0.5) 1px, transparent 1px);
  background-size: 16px 16px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
