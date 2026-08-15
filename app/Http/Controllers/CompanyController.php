<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:companies.view')->only(['index', 'show']);
        $this->middleware('can:companies.create')->only(['create', 'store']);
        $this->middleware('can:companies.edit')->only(['edit', 'update']);
        $this->middleware('can:companies.delete')->only(['destroy']);
    }
    public function index(Request $request): Response
    {
        $query = Company::query();

        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                    ->orWhere("tax_id", "like", "%{$search}%")
                    ->orWhere("invoice_series", "like", "%{$search}%");
            });
        }

        if ($request->filled("active")) {
            $query->where("active", $request->boolean("active"));
        }

        $companies = $query->orderBy("created_at", "desc")
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render("Companies/Index", [
            "companies" => $companies,
            "filters" => [
                "search" => $request->string("search")->toString(),
                "active" => $request->input("active"),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render("Companies/Create");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        if ($request->hasFile("logo_url")) {
            $validated["logo_url"] = $request->file("logo_url")->store("logos", "public");
        }

        $validated["active"] = $validated["active"] ?? true;

        Company::create($validated);

        return redirect()->route("companies.index")
            ->with("success", "Empresa creada correctamente.");
    }

    public function show(Company $company): Response
    {
        $company->load(["users", "providers", "vehicles"]);

        return Inertia::render("Companies/Show", [
            "company" => $company,
        ]);
    }

    public function edit(Company $company): Response
    {
        return Inertia::render("Companies/Edit", [
            "company" => $company,
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate($this->rules($company));

        if ($request->hasFile("logo_url")) {
            if ($company->logo_url && Storage::disk("public")->exists($company->logo_url)) {
                Storage::disk("public")->delete($company->logo_url);
            }
            $validated["logo_url"] = $request->file("logo_url")->store("logos", "public");
        }

        $company->update($validated);

        return redirect()->route("companies.index")
            ->with("success", "Empresa actualizada correctamente.");
    }

    public function destroy(Company $company): RedirectResponse
    {
        if ($company->logo_url && Storage::disk("public")->exists($company->logo_url)) {
            Storage::disk("public")->delete($company->logo_url);
        }

        $company->delete();

        return redirect()->route("companies.index")
            ->with("success", "Empresa eliminada correctamente.");
    }

    private function rules(?Company $company = null): array
    {
        return [
            "name" => "required|string|max:255",
            "tax_id" => [
                "nullable",
                "string",
                "max:50",
                $company
                ? Rule::unique("companies")->ignore($company->id)
                : Rule::unique("companies", "tax_id"),
            ],
            "invoice_series" => "nullable|string|max:50",
            "logo_url" => "nullable|image|mimes:jpg,jpeg,png,webp|max:2048",
            "active" => "boolean",
        ];
    }
}
