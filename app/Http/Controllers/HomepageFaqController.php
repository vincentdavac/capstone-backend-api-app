<?php

namespace App\Http\Controllers;

use App\Models\HomepageFaq;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageFaq;
use App\Http\Resources\HomepageFaqResource;
use App\Traits\HttpResponses;

class HomepageFaqController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return HomepageFaqResource::collection(HomepageFaq::all());
    }

    public function store(StoreHomepageFaq $request)
    {
        $validated = $request->validated();
        $faq = HomepageFaq::create($validated);

        return new HomepageFaqResource($faq);
    }

    public function show(HomepageFaq $homepageFaq)
    {
        return new HomepageFaqResource($homepageFaq);
    }

    public function update(Request $request, HomepageFaq $homepageFaq)
    {
        $homepageFaq->update($request->all());

        return new HomepageFaqResource($homepageFaq);
    }

    public function destroy(HomepageFaq $homepageFaq)
    {
        $homepageFaq->delete();

        return $this->success('', 'FAQ deleted successfully', 200);
    }
}
