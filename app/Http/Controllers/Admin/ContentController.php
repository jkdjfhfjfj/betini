<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Upload;
use App\Models\Content;
use App\Models\ContentDetails;
use App\Models\ContentMedia;
use App\Models\Language;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;


class ContentController extends Controller
{
    use Upload;

    public function index($content)
    {
        if (!array_key_exists($content, config('contents'))) {
            abort(404);
        }

        $contents = Content::with('contentDetails', 'contentMedia')->where('name', $content)->get();
        return view('admin.content.index', compact('content', 'contents'));
    }

    public function create($content)
    {
        if (!array_key_exists($content, config('contents'))) {
            abort(404);
        }
        $languages = Language::all();

        return view('admin.content.create', compact('languages', 'content'));
    }

    public function store(Request $request, $content, $language)
    {
        if (!array_key_exists($content, config('contents'))) {
            abort(404);
        }

        $purifiedData = Purify::clean($request->except('image', 'thumbnail', '_token', '_method'));

        if ($request->hasFile('image')) {
            $purifiedData['image'] = $request->file('image');
        }
        if ($request->hasFile('thumbnail')) {
            $purifiedData['thumbnail'] = $request->file('thumbnail');
        }

        $validationData = array_replace_recursive($purifiedData, $request->allFiles());
        $validate = Validator::make($validationData, config("contents.$content.validation"), config('contents.message'));

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        // save regular field
        $field_name = array_diff_key(config("contents.$content.field_name"), config("contents.content_media"));

        foreach ($field_name as $name => $type) {
            $description[$name] = $purifiedData[$name][$language];
        }

        // save content for the first time
        $contentModel = new Content();
        $contentModel->name = $content;
        $contentModel->save();

        // save content details on any language
        if ($language != 0) {
            $contentDetails = new ContentDetails();
            $contentDetails->content_id = $contentModel->id;
            $contentDetails->language_id = $language;
            $contentDetails->description = $description ? : null;
            $contentDetails->save();
        }

        // save content media
        $hasContentMedia = $this->hasContentMediaInput($request, $language);

        if ($hasContentMedia) {
            $contentMedia = new ContentMedia();
            $contentMediaDescription = [];

            foreach (config('contents.content_media') as $key => $media) {
                $uploadedFile = $request->file($key . '.' . $language);
                if ($uploadedFile) {
                    $size =  null;
                    $thumb =  null;
                    if(config("contents.$content.size.image")){
                        $size = config("contents.$content.size.image");
                    }
                    if(config("contents.$content.size.thumb")){
                        $thumb = config("contents.$content.size.thumb");
                    }

                    $contentMediaDescription[$key] = $this->uploadImage($uploadedFile, config('location.content.path'), $size, null, $thumb);
                } elseif (\Illuminate\Support\Arr::has($request->all(), $key . '.' . $language)) {
                    $mediaValue = data_get($purifiedData, $key . '.' . $language);
                    $contentMediaDescription[$key] = trim((string) $mediaValue) === '' ? null : linkToEmbed($mediaValue);
                }
            }


            $contentMedia->content_id = $contentModel->id;
            $contentMedia->description = $contentMediaDescription ?? null;
            $contentMedia->save();
        }

        return redirect(route('admin.content.index', $content))->with('success', 'Content Details Successfully Saved');
    }

    public function show(Content $content, $name = null )
    {
        if (!array_key_exists($content->name, config('contents'))) {
            abort(404);
        }

        $languages = Language::all();
        $contentDetails = ContentDetails::where('content_id', $content->id)->get()->groupBy('language_id');
        $contentMedia = ContentMedia::where('content_id', $content->id)->first();


        return view('admin.content.show', compact('content', 'languages', 'contentDetails', 'contentMedia'));
    }

    public function update(Request $request, Content $content, $language)
    {
        if (!array_key_exists($content->name, config('contents'))) {
            abort(404);
        }

        $purifiedData = Purify::clean($request->except('image', 'thumbnail', '_token', '_method'));

        if ($request->hasFile('image')) {
            $purifiedData['image'] = $request->file('image');
        }
        if ($request->hasFile('thumbnail')) {
            $purifiedData['thumbnail'] = $request->file('thumbnail');
        }

        $validationData = array_replace_recursive($purifiedData, $request->allFiles());
        $validate = Validator::make($validationData, config("contents.$content->name.validation"), config('contents.message'));
        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }

        // save regular field
        $field_name = array_diff_key(config("contents.$content->name.field_name"), config("contents.content_media"));

        foreach ($field_name as $name => $type) {
            $description[$name] = $purifiedData[$name][$language];
        }

        if ($language != 0) {
            $contentDetails = ContentDetails::where(['content_id' => $content->id, 'language_id' => $language])->firstOrNew();
            $contentDetails->content_id = $content->id;
            $contentDetails->language_id = $language;
            $contentDetails->description = $description ?? null;
            $contentDetails->save();
        }



        // save contents media
        $hasContentMedia = $this->hasContentMediaInput($request, $language);

        if ($hasContentMedia) {
            $contentMedia = ContentMedia::firstOrNew(['content_id' => $content->id]);
            $contentMediaDescription = (array)($contentMedia->description ?: []);
            $newContentMediaImages = [];
            $oldContentMediaImages = [];

            foreach (config('contents.content_media') as $key => $media) {
                $old_data = $contentMediaDescription[$key] ?? null;
                $uploadedFile = $request->file($key . '.' . $language);
                if ($uploadedFile) {
                    $size =  null;
                    $thumb =  null;
                    if(config("contents.$content->name.size.image")){
                        $size = config("contents.$content->name.size.image");
                    }
                    if(config("contents.$content->name.size.thumb")){
                        $thumb = config("contents.$content->name.size.thumb");
                    }

                    $newFilename = $this->uploadImage($uploadedFile, config('location.content.path'), $size, null, $thumb);
                    $contentMediaDescription[$key] = $newFilename;
                    $newContentMediaImages[] = $newFilename;
                    if (is_string($old_data) && $old_data !== '') {
                        $oldContentMediaImages[] = $old_data;
                    }
                } elseif (\Illuminate\Support\Arr::has($request->all(), $key . '.' . $language)) {
                    $mediaValue = data_get($purifiedData, $key . '.' . $language);
                    $contentMediaDescription[$key] = trim((string)$mediaValue) === '' ? null : linkToEmbed($mediaValue);
                } elseif (isset($old_data)) {
                    $contentMediaDescription[$key] = $old_data;
                }
            }



            $contentMedia->content_id = $content->id;
            $contentMedia->description = $contentMediaDescription ?? null;
            try {
                $contentMedia->save();
            } catch (\Throwable $exception) {
                $this->removeContentMediaImages($newContentMediaImages);
                throw $exception;
            }
            $this->removeContentMediaImages($oldContentMediaImages);
        }

        return back()->with('success', 'Content Details Successfully Saved');
    }

    /**
     * Content media fields are posted as language-indexed inputs (for example
     * image[1]), so checking only top-level file names misses uploaded images.
     */
    private function hasContentMediaInput(Request $request, $language): bool
    {
        $allInput = $request->all();

        foreach (array_keys(config('contents.content_media', [])) as $key) {
            $field = $key . '.' . $language;
            if ($request->file($field) || \Illuminate\Support\Arr::has($allInput, $field)) {
                return true;
            }
        }

        return false;
    }

    private function removeContentMediaImages(array $filenames): void
    {
        $directory = rtrim(base_path(trim(config('location.content.path'), '/\\')), '/\\');
        foreach (array_unique($filenames) as $filename) {
            if (!is_string($filename) || $filename === '') {
                continue;
            }

            $filename = basename($filename);
            $this->removeFile($directory . DIRECTORY_SEPARATOR . $filename);
            $this->removeFile($directory . DIRECTORY_SEPARATOR . 'thumb_' . $filename);
        }
    }

    public function contentDelete($id)
    {
        $content = Content::findOrFail($id);
        $content->delete();
        return back()->with('success', 'Content has been deleted');
    }

}
