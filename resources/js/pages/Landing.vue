<template>
  <Head :title="store.name">
    <meta
      name="description"
      :content="store.slogan || `${store.name} — sublimación personalizada: tazas, franelas, gorras, cojines y mucho más.`"
    />
  </Head>

  <FullScreenLayout>
    <div class="min-h-screen bg-white text-gray-800 dark:bg-gray-950 dark:text-gray-100">
      <!-- Navbar -->
      <header
        class="fixed top-0 z-50 w-full transition-all duration-300"
        :class="
          scrolled || mobileMenuOpen
            ? 'border-b border-gray-200/70 bg-white/85 shadow-theme-xs backdrop-blur-xl dark:border-white/10 dark:bg-gray-950/85'
            : 'bg-transparent'
        "
      >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-5 lg:h-18 lg:px-8">
          <a href="#inicio" class="flex shrink-0 items-center gap-2.5" @click="handleNavClick($event, '#inicio')">
            <img v-if="store.logoUrl" :src="store.logoUrl" :alt="store.name" class="h-9 w-9 rounded-xl object-cover" />
            <span v-else class="brand-gradient flex h-9 w-9 items-center justify-center rounded-xl text-white shadow-theme-sm">
              <Palette class="h-5 w-5" />
            </span>
            <span class="text-lg font-bold tracking-tight">{{ store.name }}</span>
          </a>

          <nav class="hidden items-center gap-1 lg:flex">
            <a
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white"
              @click="handleNavClick($event, link.href)"
            >
              {{ link.label }}
            </a>
          </nav>

          <div class="flex items-center gap-1.5 sm:gap-2">
            <button
              type="button"
              class="flex h-10 w-10 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white"
              aria-label="Cambiar tema"
              @click="toggleTheme"
            >
              <Sun class="hidden h-5 w-5 dark:block" />
              <Moon class="h-5 w-5 dark:hidden" />
            </button>
            <CartLink link-class="flex h-10 items-center rounded-xl px-2.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5" />
            <Link
              :href="isLoggedIn ? '/dashboard' : '/signin'"
              class="btn-secondary hidden h-10! px-4! sm:inline-flex"
            >
              <UserRound class="h-4 w-4" />
              {{ isLoggedIn ? 'Mi cuenta' : 'Ingresar' }}
            </Link>
            <button
              type="button"
              class="flex h-10 w-10 items-center justify-center rounded-xl text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5 lg:hidden"
              aria-label="Abrir menú"
              @click="mobileMenuOpen = !mobileMenuOpen"
            >
              <X v-if="mobileMenuOpen" class="h-5 w-5" />
              <Menu v-else class="h-5 w-5" />
            </button>
          </div>
        </div>

        <Transition name="slide-down">
          <div v-if="mobileMenuOpen" class="border-t border-gray-100 px-5 pb-5 pt-3 dark:border-white/10 lg:hidden">
            <nav class="flex flex-col">
              <a
                v-for="link in navLinks"
                :key="link.href"
                :href="link.href"
                class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5"
                @click="handleNavClick($event, link.href)"
              >
                {{ link.label }}
              </a>
            </nav>
            <div class="mt-3 grid grid-cols-2 gap-2">
              <Link :href="isLoggedIn ? '/dashboard' : '/signin'" class="btn-secondary">
                {{ isLoggedIn ? 'Mi cuenta' : 'Ingresar' }}
              </Link>
              <a :href="whatsappHref" target="_blank" rel="noopener" class="btn bg-[#25D366] text-white hover:bg-[#1fb857]">
                <WhatsAppIcon class="h-4 w-4" />
                WhatsApp
              </a>
            </div>
          </div>
        </Transition>
      </header>

      <!-- Hero -->
      <section id="inicio" class="relative isolate overflow-hidden pt-28 pb-20 lg:pt-36 lg:pb-28">
        <div class="pointer-events-none absolute inset-0 -z-10">
          <div class="hero-grid absolute inset-0 opacity-60 dark:opacity-25"></div>
          <div class="blob absolute -top-32 -left-24 h-[28rem] w-[28rem] rounded-full bg-brand-400/40 blur-3xl dark:bg-brand-500/25"></div>
          <div class="blob blob-delay absolute top-10 right-[-6rem] h-[26rem] w-[26rem] rounded-full bg-fuchsia-400/30 blur-3xl dark:bg-fuchsia-500/20"></div>
          <div class="blob absolute bottom-[-10rem] left-1/3 h-[22rem] w-[22rem] rounded-full bg-amber-300/40 blur-3xl dark:bg-amber-400/15"></div>
        </div>

        <div class="mx-auto grid max-w-7xl items-center gap-14 px-5 lg:grid-cols-[1.1fr_1fr] lg:px-8">
          <div>
            <span
              class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white/70 px-3.5 py-1.5 text-xs font-semibold text-brand-700 shadow-theme-xs backdrop-blur dark:border-brand-800 dark:bg-white/5 dark:text-brand-300"
            >
              <Sparkles class="h-3.5 w-3.5" />
              Sublimación de alta definición
            </span>

            <h1 class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl">
              Personaliza
              <span class="text-gradient">lo que imagines</span>
              <br class="hidden sm:block" />
              con colores que no se borran.
            </h1>

            <p class="mt-5 max-w-xl text-base text-gray-600 sm:text-lg dark:text-gray-300">
              {{ store.slogan || 'Tazas, franelas, gorras, cojines, termos y mucho más con tu diseño, tu logo o tu foto favorita. Ideal para regalos, eventos y empresas.' }}
            </p>

            <!-- Buscador -->
            <form class="relative mt-8 max-w-xl" role="search" @submit.prevent="goToSearch()">
              <div
                class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg ring-brand-500/20 transition focus-within:border-brand-300 focus-within:ring-4 dark:border-white/10 dark:bg-gray-900"
              >
                <Search class="ml-2.5 h-5 w-5 shrink-0 text-gray-400" />
                <input
                  ref="searchInput"
                  v-model="query"
                  type="search"
                  placeholder="Busca tazas, franelas, gorras..."
                  autocomplete="off"
                  aria-label="Buscar productos"
                  class="h-11 min-w-0 flex-1 border-0 bg-transparent px-1 text-base text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-0 dark:text-white"
                  @focus="searchFocused = true"
                  @blur="onSearchBlur"
                  @keydown.down.prevent="moveHighlight(1)"
                  @keydown.up.prevent="moveHighlight(-1)"
                  @keydown.esc="searchFocused = false"
                />
                <button type="submit" class="btn-primary h-11! shrink-0 px-5!">
                  <span class="hidden sm:inline">Buscar</span>
                  <ArrowRight class="h-4 w-4 sm:hidden" />
                </button>
              </div>

              <Transition name="slide-down">
                <div
                  v-if="showSuggestions"
                  class="absolute inset-x-0 top-full z-30 mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-white/10 dark:bg-gray-900"
                >
                  <ul v-if="suggestions.length" class="max-h-80 overflow-y-auto py-1.5">
                    <li v-for="(item, index) in suggestions" :key="item.id">
                      <Link
                        :href="`/catalogo/${item.slug}`"
                        class="flex items-center gap-3 px-3 py-2.5 transition"
                        :class="index === highlighted ? 'bg-brand-50 dark:bg-white/5' : 'hover:bg-gray-50 dark:hover:bg-white/5'"
                        @mouseenter="highlighted = index"
                      >
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                          <img :src="item.image ? `/storage/${item.image}` : referencePhoto(item.name, item.category)" :alt="item.name" class="h-full w-full object-cover" />
                        </span>
                        <span class="min-w-0 flex-1">
                          <span class="block truncate text-sm font-semibold text-gray-800 dark:text-white">{{ item.name }}</span>
                          <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ item.category ?? (item.type === 'servicio' ? 'Servicio' : 'Producto') }}</span>
                        </span>
                        <span class="shrink-0 text-sm font-bold text-brand-600 dark:text-brand-400">{{ formatUsd(item.price_usd) }}</span>
                      </Link>
                    </li>
                  </ul>
                  <p v-else class="px-4 py-5 text-sm text-gray-500 dark:text-gray-400">
                    No encontramos "<strong class="text-gray-700 dark:text-gray-200">{{ query }}</strong>" aún. Pulsa
                    <strong>Buscar</strong> para ver todo el catálogo o escríbenos: ¡lo hacemos a la medida!
                  </p>
                  <button
                    type="submit"
                    class="flex w-full items-center justify-between border-t border-gray-100 px-4 py-3 text-sm font-semibold text-brand-700 hover:bg-gray-50 dark:border-white/10 dark:text-brand-300 dark:hover:bg-white/5"
                  >
                    Ver todos los resultados para "{{ query }}"
                    <ArrowRight class="h-4 w-4" />
                  </button>
                </div>
              </Transition>
            </form>

            <div class="mt-4 flex flex-wrap items-center gap-2">
              <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Populares:</span>
              <button
                v-for="chip in popularChips"
                :key="chip.label"
                type="button"
                class="rounded-full border border-gray-200 bg-white/70 px-3 py-1 text-xs font-medium text-gray-600 backdrop-blur transition hover:border-brand-300 hover:text-brand-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:text-brand-300"
                @click="chip.categoryId ? goToCategory(chip.categoryId) : goToSearch(chip.label)"
              >
                {{ chip.label }}
              </button>
            </div>

            <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4">
              <div v-for="stat in stats" :key="stat.label">
                <dt class="text-xs text-gray-500 dark:text-gray-400">{{ stat.label }}</dt>
                <dd class="mt-1 text-2xl font-bold tracking-tight">{{ stat.value }}</dd>
              </div>
            </dl>
          </div>

          <!-- Visual del hero -->
          <div class="relative mx-auto w-full max-w-lg lg:max-w-none">
            <div class="grid grid-cols-2 gap-4">
              <Link
                v-for="(tile, index) in heroTiles"
                :key="tile.label"
                :href="tile.slug ? `/catalogo/${tile.slug}` : `/catalogo?search=${encodeURIComponent(tile.label)}`"
                class="group relative aspect-[4/5] overflow-hidden rounded-3xl bg-gray-100 shadow-theme-lg ring-1 ring-black/5 transition duration-500 hover:-translate-y-1 dark:bg-gray-800 dark:ring-white/10"
                :class="index % 2 === 1 ? 'translate-y-8' : ''"
              >
                <img
                  :src="tile.src"
                  :alt="tile.label"
                  :fetchpriority="index < 2 ? 'high' : 'auto'"
                  class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                />
                <div class="absolute inset-x-3 bottom-3 rounded-2xl bg-white/90 px-3.5 py-2.5 backdrop-blur dark:bg-gray-900/85">
                  <p class="truncate text-sm font-semibold text-gray-800 dark:text-white">{{ tile.label }}</p>
                  <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ tile.caption }}</p>
                </div>
              </Link>
            </div>

            <div class="float-slow absolute -left-4 top-1/3 hidden rounded-2xl bg-white px-4 py-3 shadow-theme-xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 sm:block">
              <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-success-50 text-success-600 dark:bg-success-500/15">
                  <BadgeCheck class="h-5 w-5" />
                </span>
                <div>
                  <p class="text-sm font-semibold">Boceto gratis</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">Antes de imprimir</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Beneficios -->
      <section class="border-y border-gray-100 bg-gray-50/70 dark:border-white/5 dark:bg-white/[0.02]">
        <div class="mx-auto grid max-w-7xl gap-6 px-5 py-8 sm:grid-cols-2 lg:grid-cols-4 lg:px-8">
          <div v-for="perk in perks" :key="perk.title" class="flex items-center gap-3.5">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 shadow-theme-xs ring-1 ring-gray-100 dark:bg-gray-900 dark:text-brand-400 dark:ring-white/10">
              <component :is="perk.icon" class="h-5 w-5" />
            </span>
            <div>
              <p class="text-sm font-semibold">{{ perk.title }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ perk.text }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Categorías -->
      <section id="categorias" class="mx-auto max-w-7xl scroll-mt-24 px-5 py-20 lg:px-8">
        <div v-reveal class="reveal flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
          <div>
            <p class="section-eyebrow">Explora</p>
            <h2 class="section-title">¿Qué quieres personalizar?</h2>
          </div>
          <Link href="/catalogo" class="btn-secondary self-start sm:self-auto">
            Ver catálogo completo
            <ArrowRight class="h-4 w-4" />
          </Link>
        </div>

        <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
          <component
            :is="Link"
            v-for="(cat, index) in categoryCards"
            :key="cat.key"
            v-reveal
            :href="cat.href"
            :style="{ transitionDelay: `${index * 50}ms` }"
            class="reveal group relative isolate flex aspect-[4/5] flex-col justify-end overflow-hidden rounded-3xl bg-gray-100 p-5 text-white shadow-theme-xs transition hover:-translate-y-1 hover:shadow-theme-lg dark:bg-gray-800 sm:aspect-square"
          >
            <img
              :src="cat.photo"
              :alt="cat.name"
              loading="lazy"
              class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-700 group-hover:scale-110"
            />
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-gray-950/85 via-gray-950/25 to-transparent"></div>
            <span class="absolute left-4 top-4 flex h-10 w-10 items-center justify-center rounded-xl bg-white/90 text-brand-600 shadow-theme-xs backdrop-blur transition duration-300 group-hover:bg-brand-500 group-hover:text-white">
              <component :is="cat.icon" class="h-5 w-5" />
            </span>
            <ArrowUpRight class="absolute right-4 top-4 h-5 w-5 text-white/70 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-white" />
            <p class="text-lg font-bold leading-tight">{{ cat.name }}</p>
            <p class="mt-0.5 text-xs text-white/75">{{ cat.caption }}</p>
          </component>
        </div>
      </section>

      <!-- Productos destacados -->
      <section id="destacados" class="scroll-mt-24 bg-gray-50/70 py-20 dark:bg-white/[0.02]">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
          <div v-reveal class="reveal flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
              <p class="section-eyebrow">Lo más pedido</p>
              <h2 class="section-title">Productos destacados</h2>
            </div>
            <Link href="/catalogo" class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
              Ver todos
              <ArrowRight class="h-4 w-4" />
            </Link>
          </div>

          <div v-if="featuredProducts.length" class="mt-10 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
            <Link
              v-for="(product, index) in featuredProducts"
              :key="product.id"
              v-reveal
              :href="`/catalogo/${product.slug}`"
              :style="{ transitionDelay: `${index * 50}ms` }"
              class="reveal group flex flex-col overflow-hidden rounded-3xl border border-gray-100 bg-white transition hover:-translate-y-1 hover:shadow-theme-lg dark:border-white/10 dark:bg-gray-900"
            >
              <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
                <img
                  :src="product.images[0] ? `/storage/${product.images[0].path}` : referencePhoto(product.name, product.category?.name)"
                  :alt="product.name"
                  loading="lazy"
                  class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <span
                  v-if="!product.images[0]"
                  class="absolute bottom-3 right-3 rounded-full bg-gray-900/60 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur"
                >
                  Imagen referencial
                </span>
                <div class="absolute left-3 top-3 flex gap-1.5">
                  <span v-if="product.featured" class="rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-bold text-amber-950 shadow-theme-xs">Destacado</span>
                  <span v-if="product.type === 'servicio'" class="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-bold text-gray-700 shadow-theme-xs">Servicio</span>
                </div>
              </div>
              <div class="flex flex-1 flex-col p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.category?.name ?? '—' }}</p>
                <h3 class="mt-1 line-clamp-2 font-semibold leading-snug">{{ product.name }}</h3>
                <div class="mt-auto flex items-center justify-between pt-3">
                  <p class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ priceLabel(product) }}</p>
                  <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition group-hover:bg-brand-500 group-hover:text-white dark:bg-white/5 dark:text-gray-300">
                    <ShoppingBag class="h-4 w-4" />
                  </span>
                </div>
              </div>
            </Link>
          </div>

          <div v-else class="mt-10 rounded-3xl border-2 border-dashed border-gray-200 px-6 py-14 text-center dark:border-white/10">
            <PackageOpen class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" />
            <p class="mt-3 font-semibold">Pronto verás nuestros productos aquí</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mientras tanto, escríbenos y te cotizamos lo que necesites.</p>
            <a :href="whatsappHref" target="_blank" rel="noopener" class="btn-primary mt-5">
              <WhatsAppIcon class="h-4 w-4" />
              Pedir cotización
            </a>
          </div>
        </div>
      </section>

      <!-- Cómo funciona -->
      <section id="como-funciona" class="mx-auto max-w-7xl scroll-mt-24 px-5 py-20 lg:px-8">
        <div v-reveal class="reveal mx-auto max-w-2xl text-center">
          <p class="section-eyebrow">Fácil y rápido</p>
          <h2 class="section-title">Tu pedido en 4 pasos</h2>
          <p class="mt-3 text-gray-500 dark:text-gray-400">Tú pones la idea, nosotros nos encargamos del resto.</p>
        </div>

        <ol class="relative mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="absolute left-0 right-0 top-8 hidden h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent dark:via-white/10 lg:block"></div>
          <li
            v-for="(step, index) in steps"
            :key="step.title"
            v-reveal
            :style="{ transitionDelay: `${index * 90}ms` }"
            class="reveal relative rounded-3xl border border-gray-100 bg-white p-6 dark:border-white/10 dark:bg-gray-900"
          >
            <span class="brand-gradient relative flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-theme-md">
              <component :is="step.icon" class="h-6 w-6" />
            </span>
            <span class="absolute right-6 top-6 text-4xl font-black text-gray-100 dark:text-white/5">0{{ index + 1 }}</span>
            <h3 class="mt-5 font-semibold">{{ step.title }}</h3>
            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">{{ step.text }}</p>
          </li>
        </ol>
      </section>

      <!-- Empresas / al mayor -->
      <section class="mx-auto max-w-7xl px-5 lg:px-8">
        <div v-reveal class="reveal brand-gradient relative isolate overflow-hidden rounded-[2rem] px-6 py-14 text-white sm:px-12 lg:py-16">
          <div class="pointer-events-none absolute -right-20 -top-20 -z-10 h-80 w-80 rounded-full bg-white/15 blur-2xl"></div>
          <div class="pointer-events-none absolute -bottom-24 left-10 -z-10 h-72 w-72 rounded-full bg-fuchsia-400/30 blur-3xl"></div>
          <div class="grid items-center gap-8 lg:grid-cols-[1.4fr_1fr]">
            <div>
              <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/25">
                <Building2 class="h-3.5 w-3.5" />
                Empresas, eventos y emprendedores
              </span>
              <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">¿Pedidos al mayor? Tenemos precios especiales.</h2>
              <p class="mt-3 max-w-xl text-white/85">
                Merch corporativo, recuerdos para cumpleaños, bodas, graduaciones y uniformes. Cotiza sin compromiso y
                recibe tu boceto en tiempo récord.
              </p>
            </div>
            <div class="flex flex-wrap gap-3 lg:justify-end">
              <a :href="whatsappHref" target="_blank" rel="noopener" class="btn bg-white text-gray-900 shadow-theme-md hover:bg-white/90">
                <WhatsAppIcon class="h-5 w-5 text-[#25D366]" />
                Cotizar por WhatsApp
              </a>
              <Link href="/catalogo" class="btn bg-white/10 text-white ring-1 ring-white/40 hover:bg-white/20">
                Ver catálogo
              </Link>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section id="faq" class="mx-auto max-w-3xl scroll-mt-24 px-5 py-20 lg:px-8">
        <div v-reveal class="reveal text-center">
          <p class="section-eyebrow">Dudas</p>
          <h2 class="section-title">Preguntas frecuentes</h2>
        </div>

        <div class="mt-10 space-y-3">
          <div
            v-for="(faq, index) in faqs"
            :key="faq.q"
            class="overflow-hidden rounded-2xl border bg-white transition dark:bg-gray-900"
            :class="openFaq === index ? 'border-brand-200 shadow-theme-sm dark:border-brand-800' : 'border-gray-100 dark:border-white/10'"
          >
            <button
              type="button"
              class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
              :aria-expanded="openFaq === index"
              @click="openFaq = openFaq === index ? null : index"
            >
              <span class="text-sm font-semibold sm:text-base">{{ faq.q }}</span>
              <span
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full transition"
                :class="openFaq === index ? 'rotate-45 bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/5'"
              >
                <Plus class="h-4 w-4" />
              </span>
            </button>
            <div v-show="openFaq === index" class="px-5 pb-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
              {{ faq.a }}
            </div>
          </div>
        </div>
      </section>

      <!-- Contacto -->
      <section id="contacto" class="scroll-mt-24 bg-gray-50/70 py-20 dark:bg-white/[0.02]">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 lg:grid-cols-[1fr_1.1fr] lg:px-8">
          <div v-reveal class="reveal">
            <p class="section-eyebrow">Contacto</p>
            <h2 class="section-title">Hagamos realidad tu idea</h2>
            <p class="mt-3 text-gray-500 dark:text-gray-400">Cuéntanos qué necesitas y te respondemos con precio y tiempo de entrega.</p>

            <ul class="mt-8 space-y-3">
              <li v-if="store.social.whatsapp">
                <a :href="whatsappHref" target="_blank" rel="noopener" class="contact-row">
                  <span class="contact-icon bg-[#25D366]/10 text-[#25D366]"><WhatsAppIcon class="h-5 w-5" /></span>
                  <span><span class="contact-label">WhatsApp</span>{{ store.social.whatsapp }}</span>
                </a>
              </li>
              <li v-if="store.contact.email">
                <a :href="`mailto:${store.contact.email}`" class="contact-row">
                  <span class="contact-icon"><Mail class="h-5 w-5" /></span>
                  <span><span class="contact-label">Correo</span>{{ store.contact.email }}</span>
                </a>
              </li>
              <li v-if="store.contact.address">
                <a :href="mapsHref" target="_blank" rel="noopener" class="contact-row">
                  <span class="contact-icon"><MapPin class="h-5 w-5" /></span>
                  <span><span class="contact-label">Dirección</span>{{ store.contact.address }}</span>
                </a>
              </li>
              <li v-if="store.contact.hours">
                <div class="contact-row">
                  <span class="contact-icon"><Clock class="h-5 w-5" /></span>
                  <span><span class="contact-label">Horario</span>{{ store.contact.hours }}</span>
                </div>
              </li>
            </ul>

            <div v-if="hasSocial" class="mt-6 flex items-center gap-2">
              <a v-if="store.social.instagram" :href="store.social.instagram" target="_blank" rel="noopener" aria-label="Instagram" class="social-pill"><Instagram class="h-5 w-5" /></a>
              <a v-if="store.social.facebook" :href="store.social.facebook" target="_blank" rel="noopener" aria-label="Facebook" class="social-pill"><Facebook class="h-5 w-5" /></a>
              <a v-if="store.social.tiktok" :href="store.social.tiktok" target="_blank" rel="noopener" aria-label="TikTok" class="social-pill"><TikTokIcon class="h-5 w-5" /></a>
            </div>
          </div>

          <form v-reveal class="reveal rounded-3xl border border-gray-100 bg-white p-6 shadow-theme-sm dark:border-white/10 dark:bg-gray-900 sm:p-8" @submit.prevent="sendContact">
            <h3 class="text-lg font-semibold">Pide tu cotización</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Te abriremos WhatsApp con el mensaje listo para enviar.</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
              <div class="sm:col-span-2">
                <label class="label" for="c-nombre">Nombre</label>
                <input id="c-nombre" v-model="contactForm.nombre" required class="field" placeholder="Tu nombre" />
              </div>
              <div>
                <label class="label" for="c-producto">Producto</label>
                <select id="c-producto" v-model="contactForm.producto" class="field">
                  <option value="">Selecciona...</option>
                  <option v-for="cat in categoryCards" :key="cat.key" :value="cat.name">{{ cat.name }}</option>
                  <option value="Otro">Otro</option>
                </select>
              </div>
              <div>
                <label class="label" for="c-cantidad">Cantidad aprox.</label>
                <input id="c-cantidad" v-model="contactForm.cantidad" type="number" min="1" class="field" placeholder="Ej. 20" />
              </div>
              <div class="sm:col-span-2">
                <label class="label" for="c-mensaje">Cuéntanos tu idea</label>
                <textarea id="c-mensaje" v-model="contactForm.mensaje" rows="4" required class="field h-auto! py-3 resize-none" placeholder="Colores, tamaño, fecha de entrega..."></textarea>
              </div>
            </div>

            <button type="submit" class="btn-primary mt-6 w-full">
              <Send class="h-4 w-4" />
              Enviar por WhatsApp
            </button>
          </form>
        </div>
      </section>

      <!-- Footer -->
      <footer class="border-t border-gray-100 dark:border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-5 py-12 lg:flex-row lg:items-start lg:justify-between lg:px-8">
          <div class="max-w-sm">
            <div class="flex items-center gap-2.5">
              <img v-if="store.logoUrl" :src="store.logoUrl" :alt="store.name" class="h-9 w-9 rounded-xl object-cover" />
              <span v-else class="brand-gradient flex h-9 w-9 items-center justify-center rounded-xl text-white"><Palette class="h-5 w-5" /></span>
              <span class="text-lg font-bold">{{ store.name }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
              {{ store.slogan || 'Sublimación personalizada con calidad profesional para regalos, eventos y empresas.' }}
            </p>
          </div>

          <div class="grid grid-cols-2 gap-10 sm:grid-cols-3">
            <div>
              <h4 class="footer-title">Tienda</h4>
              <ul class="mt-3 space-y-2 text-sm">
                <li><Link href="/catalogo" class="footer-link">Catálogo</Link></li>
                <li><a href="#categorias" class="footer-link" @click="handleNavClick($event, '#categorias')">Categorías</a></li>
                <li><a href="#destacados" class="footer-link" @click="handleNavClick($event, '#destacados')">Destacados</a></li>
              </ul>
            </div>
            <div>
              <h4 class="footer-title">Ayuda</h4>
              <ul class="mt-3 space-y-2 text-sm">
                <li><a href="#como-funciona" class="footer-link" @click="handleNavClick($event, '#como-funciona')">Cómo funciona</a></li>
                <li><a href="#faq" class="footer-link" @click="handleNavClick($event, '#faq')">Preguntas frecuentes</a></li>
                <li><a href="#contacto" class="footer-link" @click="handleNavClick($event, '#contacto')">Contacto</a></li>
              </ul>
            </div>
            <div>
              <h4 class="footer-title">Legal</h4>
              <ul class="mt-3 space-y-2 text-sm">
                <li><Link href="/privacidad" class="footer-link">Privacidad</Link></li>
                <li><Link href="/cookies" class="footer-link">Cookies</Link></li>
                <li><Link href="/terminos" class="footer-link">Términos</Link></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="border-t border-gray-100 py-5 text-center text-xs text-gray-500 dark:border-white/10 dark:text-gray-500">
          &copy; {{ year }} {{ store.name }}. Todos los derechos reservados.
        </div>
      </footer>
    </div>

    <!-- Botón flotante de WhatsApp -->
    <a
      v-if="store.social.whatsapp"
      :href="whatsappHref"
      target="_blank"
      rel="noopener"
      aria-label="Escríbenos por WhatsApp"
      class="fixed right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-theme-xl transition-all hover:scale-105"
      :class="showCookieBanner ? 'bottom-28 sm:bottom-24' : 'bottom-5'"
    >
      <WhatsAppIcon class="h-7 w-7" />
    </a>

    <!-- Aviso de cookies -->
    <Transition name="fade">
      <div
        v-if="showCookieBanner"
        class="fixed inset-x-3 bottom-3 z-50 mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-theme-xl backdrop-blur dark:border-white/10 dark:bg-gray-900/95"
      >
        <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
          <p class="text-sm text-gray-600 dark:text-gray-300">
            Usamos cookies para mejorar tu experiencia.
            <Link href="/cookies" class="font-medium text-brand-600 underline dark:text-brand-400">Más información</Link>
          </p>
          <div class="flex shrink-0 gap-2">
            <button type="button" class="btn-secondary h-9! px-4!" @click="setCookieConsent('rejected')">Rechazar</button>
            <button type="button" class="btn-primary h-9! px-4!" @click="setCookieConsent('accepted')">Aceptar</button>
          </div>
        </div>
      </div>
    </Transition>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { computed, h, onMounted, onUnmounted, ref, watch, type Component } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue'
import CartLink from '@/components/common/CartLink.vue'
import { useTheme } from '@/components/layout/ThemeProvider.vue'
import {
  ArrowRight,
  ArrowUpRight,
  BadgeCheck,
  Building2,
  Clock,
  Coffee,
  CreditCard,
  Facebook,
  Frame,
  Gift,
  GlassWater,
  Instagram,
  Sofa,
  Mail,
  MapPin,
  Menu,
  MessageCircle,
  Moon,
  PackageOpen,
  Palette,
  PenTool,
  Plus,
  Search,
  Send,
  Shirt,
  ShoppingBag,
  Sparkles,
  Sun,
  Truck,
  UserRound,
  X,
} from 'lucide-vue-next'

interface ProductCard {
  id: number
  slug: string
  name: string
  type: 'producto' | 'servicio'
  featured: boolean
  price_usd: string | number
  category: { id: number; name: string } | null
  images: Array<{ id: number; path: string }>
  variants: Array<{ id: number; price_override: string | number | null }>
}

interface SearchItem {
  id: number
  name: string
  slug: string
  type: 'producto' | 'servicio'
  price_usd: string | number
  category: string | null
  image: string | null
}

const props = withDefaults(
  defineProps<{
    featuredProducts?: ProductCard[]
    categories?: Array<{ id: number; name: string; slug: string; products_count: number }>
    searchIndex?: SearchItem[]
  }>(),
  { featuredProducts: () => [], categories: () => [], searchIndex: () => [] },
)

const { toggleTheme } = useTheme()

const page = usePage<{
  auth: { user: unknown | null }
  store: {
    name: string
    slogan: string | null
    logoUrl: string | null
    social: { instagram: string | null; facebook: string | null; tiktok: string | null; whatsapp: string | null }
    contact: { email: string | null; address: string | null; hours: string | null }
  }
}>()
const store = computed(() => page.props.store)
const isLoggedIn = computed(() => !!page.props.auth?.user)
const hasSocial = computed(() => !!(store.value.social.instagram || store.value.social.facebook || store.value.social.tiktok))
const year = new Date().getFullYear()

// --- Navbar ---
const scrolled = ref(false)
const mobileMenuOpen = ref(false)
const onScroll = () => {
  scrolled.value = window.scrollY > 20
}

const navLinks = [
  { href: '/catalogo', label: 'Catálogo' },
  { href: '#categorias', label: 'Categorías' },
  { href: '#destacados', label: 'Destacados' },
  { href: '#como-funciona', label: 'Cómo funciona' },
  { href: '#faq', label: 'FAQ' },
  { href: '#contacto', label: 'Contacto' },
]

const handleNavClick = (event: MouseEvent, href: string) => {
  mobileMenuOpen.value = false
  if (!href.startsWith('#')) {
    event.preventDefault()
    router.visit(href)
    return
  }
  event.preventDefault()
  document.querySelector(href)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  onScroll()
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))

// --- Formato de precios ---
const usd = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const formatUsd = (value: string | number) => `$${usd.format(Number(value))}`

const priceLabel = (product: ProductCard) => {
  const prices = [
    Number(product.price_usd),
    ...product.variants.filter((v) => v.price_override !== null).map((v) => Number(v.price_override)),
  ]
  const min = Math.min(...prices)
  const max = Math.max(...prices)
  return min === max ? formatUsd(min) : `Desde ${formatUsd(min)}`
}

// --- Buscador con sugerencias instantáneas ---
const query = ref('')
const searchFocused = ref(false)
const highlighted = ref(-1)
const searchInput = ref<HTMLInputElement | null>(null)

const normalize = (text: string) =>
  text
    .toLowerCase()
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')

const suggestions = computed(() => {
  const terms = normalize(query.value.trim()).split(/\s+/).filter(Boolean)
  if (!terms.length) return []
  return props.searchIndex
    .filter((item) => {
      const haystack = normalize(`${item.name} ${item.category ?? ''}`)
      return terms.every((term) => haystack.includes(term))
    })
    .slice(0, 6)
})

const showSuggestions = computed(() => searchFocused.value && query.value.trim().length > 1)

watch(query, () => {
  highlighted.value = -1
})

const moveHighlight = (delta: number) => {
  if (!suggestions.value.length) return
  const total = suggestions.value.length
  highlighted.value = (highlighted.value + delta + total) % total
}

const onSearchBlur = () => {
  // Deja tiempo a que el clic en una sugerencia se registre antes de cerrar el panel
  window.setTimeout(() => (searchFocused.value = false), 150)
}

const goToSearch = (term?: string) => {
  const selected = term === undefined && highlighted.value >= 0 ? suggestions.value[highlighted.value] : null
  if (selected) {
    router.visit(`/catalogo/${selected.slug}`)
    return
  }
  const value = (term ?? query.value).trim()
  router.get('/catalogo', value ? { search: value } : {})
}

const goToCategory = (id: number) => router.get('/catalogo', { category_id: id })

// --- Contenido ---
// Fotos de referencia en public/images/landing (Unsplash, licencia libre)
const fallbackCategories: Array<{ name: string; icon: Component; photo: string; caption: string; keys: string[] }> = [
  { name: 'Franelas', icon: Shirt, photo: '/images/landing/tee-print.jpg', caption: 'Poliéster full color', keys: ['franela', 'camisa', 'textil', 'ropa', 'shirt', 'chemise'] },
  { name: 'Tazas', icon: Coffee, photo: '/images/landing/mug.jpg', caption: 'Mágicas, blancas y de colores', keys: ['taza', 'mug', 'jarra'] },
  { name: 'Gorras', icon: Sparkles, photo: '/images/landing/cap.jpg', caption: 'Tipo trucker y deportivas', keys: ['gorra', 'cap', 'sombrero'] },
  { name: 'Cojines', icon: Sofa, photo: '/images/landing/pillow.jpg', caption: 'Con tu foto favorita', keys: ['cojin', 'almohada', 'pillow'] },
  { name: 'Termos', icon: GlassWater, photo: '/images/landing/bottle.jpg', caption: 'Acero y aluminio', keys: ['termo', 'botella', 'vaso', 'tumbler'] },
  { name: 'Cuadros', icon: Frame, photo: '/images/landing/frame.jpg', caption: 'Placas y fotopaneles', keys: ['cuadro', 'placa', 'foto', 'lamina', 'marco'] },
  { name: 'Regalos', icon: Gift, photo: '/images/landing/gift.jpg', caption: 'Combos y detalles', keys: ['regalo', 'combo', 'detalle', 'kit'] },
  { name: 'Diseño', icon: PenTool, photo: '/images/landing/design.jpg', caption: 'Logos y artes a medida', keys: ['diseno', 'servicio', 'logo', 'arte'] },
]

const categoryCards = computed(() => {
  if (!props.categories.length) {
    return fallbackCategories.map((c) => ({ ...c, key: c.name, href: `/catalogo?search=${encodeURIComponent(c.name)}` }))
  }
  return props.categories.slice(0, 8).map((cat, index) => {
    const style = pickCategoryStyle(cat.name, index)
    return {
      key: String(cat.id),
      name: cat.name,
      href: `/catalogo?category_id=${cat.id}`,
      caption: cat.products_count === 1 ? '1 producto' : `${cat.products_count} productos`,
      icon: style.icon,
      photo: style.photo,
    }
  })
})

// Asigna icono y foto coherentes según el nombre (categoría o producto)
function findCategoryStyle(name: string) {
  const n = normalize(name)
  return fallbackCategories.find((c) => c.keys.some((k) => n.includes(k))) ?? null
}
function pickCategoryStyle(name: string, index: number) {
  return findCategoryStyle(name) ?? fallbackCategories[index % fallbackCategories.length]
}

// Foto para productos que aún no tienen imagen propia
const referencePhoto = (name: string, category?: string | null) =>
  (findCategoryStyle(`${name} ${category ?? ''}`) ?? fallbackCategories[0]).photo

const popularChips = computed(() =>
  props.categories.length
    ? props.categories.slice(0, 5).map((c) => ({ label: c.name, categoryId: c.id }))
    : ['Tazas', 'Franelas', 'Termos', 'Gorras', 'Cojines'].map((label) => ({ label, categoryId: null })),
)

// Primero productos reales con foto; se completa con fotos de referencia hasta 4
const heroTiles = computed(() => {
  const fromProducts = props.featuredProducts
    .filter((p) => p.images[0])
    .slice(0, 4)
    .map((p) => ({ label: p.name, caption: priceLabel(p), slug: p.slug as string | null, src: `/storage/${p.images[0].path}` }))
  const fromPhotos = fallbackCategories
    .slice(0, 4 - fromProducts.length)
    .map((c) => ({ label: c.name, caption: c.caption, slug: null as string | null, src: c.photo }))
  return [...fromProducts, ...fromPhotos]
})

const stats = computed(() => [
  { label: 'Productos', value: props.searchIndex.length ? `${props.searchIndex.length}+` : '50+' },
  { label: 'Categorías', value: props.categories.length || '8' },
  { label: 'Entrega desde', value: '24 h' },
])

const perks = [
  { icon: Sparkles, title: 'Colores HD', text: 'Impresión vibrante y duradera' },
  { icon: PenTool, title: 'Diseño incluido', text: 'Te enviamos el boceto' },
  { icon: Truck, title: 'Envíos nacionales', text: 'Delivery y encomiendas' },
  { icon: CreditCard, title: 'Paga como quieras', text: 'Pago móvil, transferencia o PayPal' },
]

const steps = [
  { icon: Search, title: 'Elige tu producto', text: 'Explora el catálogo y escoge lo que quieres personalizar.' },
  { icon: MessageCircle, title: 'Envía tu diseño', text: 'Comparte tu foto, logo o idea. Si no tienes, lo diseñamos.' },
  { icon: BadgeCheck, title: 'Aprueba el boceto', text: 'Te mostramos cómo quedará antes de imprimir.' },
  { icon: ShoppingBag, title: 'Recibe tu pedido', text: 'Retíralo en tienda o te lo enviamos a donde estés.' },
]

const openFaq = ref<number | null>(0)
const faqs = [
  {
    q: '¿Qué es la sublimación y cuánto dura?',
    a: 'Es una técnica en la que la tinta se transfiere con calor y pasa a formar parte del material. Por eso no se pela ni se agrieta: con el cuidado adecuado dura años, incluso con lavados frecuentes.',
  },
  {
    q: '¿Puedo enviar mi propio diseño o foto?',
    a: 'Sí. Recibimos imágenes en JPG, PNG o PDF. Mientras mejor sea la resolución, mejor el resultado. Si no tienes diseño, nuestro equipo te ayuda a crearlo.',
  },
  {
    q: '¿Cuánto tarda mi pedido?',
    a: 'Los pedidos unitarios suelen estar listos en 24 a 72 horas luego de aprobar el boceto. Para pedidos al mayor te indicamos el tiempo exacto al cotizar.',
  },
  {
    q: '¿Hacen pedidos al mayor o para empresas?',
    a: 'Sí, manejamos precios especiales por volumen para empresas, eventos, colegios y emprendedores. Escríbenos para cotizar.',
  },
  {
    q: '¿Qué métodos de pago aceptan?',
    a: 'Aceptamos pago móvil, transferencia bancaria, efectivo y PayPal. Puedes pagar en dólares o en bolívares a la tasa del día.',
  },
  {
    q: '¿Hacen envíos?',
    a: 'Sí, hacemos delivery en la ciudad y envíos nacionales por encomienda. El costo depende del destino.',
  },
]

// --- Contacto vía WhatsApp ---
const whatsappNumber = computed(() => (store.value.social.whatsapp ?? '').replace(/\D/g, ''))
const buildWhatsapp = (text: string) => `https://wa.me/${whatsappNumber.value}?text=${encodeURIComponent(text)}`
const whatsappHref = computed(() => buildWhatsapp(`Hola ${store.value.name}, quisiera cotizar un producto personalizado.`))

const contactForm = ref({ nombre: '', producto: '', cantidad: '', mensaje: '' })
const sendContact = () => {
  const f = contactForm.value
  const lines = [
    `Hola, soy ${f.nombre}.`,
    f.producto ? `Me interesa: ${f.producto}` : null,
    f.cantidad ? `Cantidad aprox.: ${f.cantidad}` : null,
    f.mensaje,
  ].filter(Boolean)
  window.open(buildWhatsapp(lines.join('\n')), '_blank', 'noopener')
}

const mapsHref = computed(
  () => `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(store.value.contact.address ?? '')}`,
)

// --- Aviso de cookies ---
const cookieConsentKey = 'company_cookie_consent'
const showCookieBanner = ref(false)
onMounted(() => {
  try {
    showCookieBanner.value = !localStorage.getItem(cookieConsentKey)
  } catch {
    showCookieBanner.value = false
  }
})
const setCookieConsent = (value: 'accepted' | 'rejected') => {
  try {
    localStorage.setItem(cookieConsentKey, value)
  } catch {
    // almacenamiento no disponible: solo ocultamos el aviso
  }
  showCookieBanner.value = false
}

// --- Animación al entrar en pantalla ---
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
      { threshold: 0.12 },
    )
    observer.observe(el)
  },
}

// --- Iconos de marcas (no incluidos en lucide) ---
const WhatsAppIcon = (p: { class?: string }) =>
  h('svg', { class: p.class, viewBox: '0 0 24 24', fill: 'currentColor', xmlns: 'http://www.w3.org/2000/svg' }, [
    h('path', {
      d: 'M12.04 2c-5.52 0-10 4.48-10 10 0 1.77.46 3.45 1.26 4.9L2 22l5.25-1.28A9.96 9.96 0 0 0 12.04 22c5.52 0 10-4.48 10-10s-4.48-10-10-10Zm0 18.2c-1.6 0-3.1-.43-4.4-1.2l-.32-.19-3.11.76.76-3.03-.2-.31A8.17 8.17 0 0 1 3.84 12c0-4.53 3.68-8.2 8.2-8.2 4.53 0 8.2 3.67 8.2 8.2 0 4.52-3.67 8.2-8.2 8.2Zm4.48-6.14c-.24-.12-1.44-.71-1.67-.79-.22-.08-.38-.12-.55.12-.16.24-.63.79-.77.95-.14.16-.28.18-.52.06-.24-.12-1.01-.37-1.92-1.18-.71-.63-1.19-1.42-1.33-1.66-.14-.24-.02-.37.1-.49.11-.11.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.55-1.32-.75-1.81-.2-.48-.4-.41-.55-.42-.14-.01-.3-.01-.46-.01a.9.9 0 0 0-.65.3c-.22.24-.85.83-.85 2.03 0 1.2.87 2.36.99 2.52.12.16 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.18 1.11.16 1.53.1.47-.07 1.44-.59 1.64-1.16.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z',
    }),
  ])

const TikTokIcon = (p: { class?: string }) =>
  h('svg', { class: p.class, viewBox: '0 0 24 24', fill: 'currentColor', xmlns: 'http://www.w3.org/2000/svg' }, [
    h('path', {
      d: 'M16.6 5.82c-.94-.66-1.53-1.72-1.6-2.82h-2.79v13.44c0 1.34-1.09 2.43-2.43 2.43a2.43 2.43 0 1 1 0-4.86c.24 0 .48.03.7.1V11c-.23-.03-.46-.05-.7-.05a5.24 5.24 0 1 0 5.23 5.24V9.13a7.98 7.98 0 0 0 4.6 1.46V7.8a4.85 4.85 0 0 1-2.81-1.98Z',
    }),
  ])
</script>

<style scoped>
@reference '../../css/app.css';

.brand-gradient {
  background-image: linear-gradient(135deg, var(--color-primary), var(--color-secondary) 55%, #7c3aed);
}

.text-gradient {
  background-image: linear-gradient(90deg, var(--color-primary), #d946ef 55%, #f59e0b);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.hero-grid {
  background-image:
    linear-gradient(to right, rgb(148 163 184 / 0.18) 1px, transparent 1px),
    linear-gradient(to bottom, rgb(148 163 184 / 0.18) 1px, transparent 1px);
  background-size: 44px 44px;
  mask-image: radial-gradient(ellipse at 50% 30%, black 30%, transparent 75%);
}

.section-eyebrow {
  @apply text-xs font-bold tracking-[0.18em] text-brand-600 uppercase dark:text-brand-400;
}
.section-title {
  @apply mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl;
}

.field {
  @apply h-11 w-full rounded-xl border border-gray-300 bg-white px-3.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-400 focus:ring-4 focus:ring-brand-500/15 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white;
}

.contact-row {
  @apply flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-4 text-sm font-medium transition hover:border-brand-200 hover:shadow-theme-sm dark:border-white/10 dark:bg-gray-900;
}
.contact-icon {
  @apply flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400;
}
.contact-label {
  @apply block text-xs font-normal text-gray-500 dark:text-gray-400;
}
.social-pill {
  @apply flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300;
}

.footer-title {
  @apply text-xs font-bold tracking-wider text-gray-900 uppercase dark:text-white;
}
.footer-link {
  @apply text-gray-500 transition hover:text-brand-600 dark:text-gray-400 dark:hover:text-brand-400;
}

@keyframes float-slow {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}
.float-slow {
  animation: float-slow 5s ease-in-out infinite;
}

@keyframes blob {
  0%,
  100% {
    transform: translate(0, 0) scale(1);
  }
  50% {
    transform: translate(30px, -20px) scale(1.08);
  }
}
.blob {
  animation: blob 14s ease-in-out infinite;
}
.blob-delay {
  animation-delay: -7s;
}

.reveal {
  opacity: 0;
  transform: translateY(20px);
  transition:
    opacity 0.6s ease,
    transform 0.6s ease;
}
.reveal-visible {
  opacity: 1;
  transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
  .blob,
  .float-slow {
    animation: none;
  }
  .reveal {
    opacity: 1;
    transform: none;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition:
    opacity 0.18s ease,
    transform 0.18s ease;
}
.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
