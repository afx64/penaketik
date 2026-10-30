<?php

namespace Tests\Feature;

use App\Models\AccessGrant;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChapterAccessTest extends TestCase
{
    use RefreshDatabase;

    private Book $book;

    private User $author;

    private User $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->author = User::factory()->create();
        $this->reader = User::factory()->create();

        $this->book = Book::create([
            'user_id' => $this->author->id,
            'title' => 'Tide Test',
            'slug' => 'tide-test',
            'synopsis' => 'A book used by the access tests.',
            'status' => 'published',
            'price' => 50000,
            'currency' => 'IDR',
            'language' => 'en',
            'published_at' => now(),
        ]);
    }

    private function chapter(string $title, int $position, string $access): Chapter
    {
        return $this->book->chapters()->create([
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'position' => $position,
            'access' => $access,
            'content' => '<p>Secret body of '.$title.'</p>',
            'is_published' => true,
        ]);
    }

    public function test_guest_can_read_only_the_first_three_public_chapters(): void
    {
        $first = $this->chapter('First', 1, 'public');
        $fourth = $this->chapter('Fourth', 4, 'public');

        $this->get(route('books.read', [$this->book, $first]))
            ->assertOk()
            ->assertSee('Secret body of First');

        $this->get(route('books.read', [$this->book, $fourth]))
            ->assertStatus(403)
            ->assertDontSee('Secret body of Fourth');
    }

    public function test_free_chapter_requires_a_login(): void
    {
        $free = $this->chapter('Free One', 5, 'free');

        $this->get(route('books.read', [$this->book, $free]))->assertStatus(403);

        $this->actingAs($this->reader)
            ->get(route('books.read', [$this->book, $free]))
            ->assertOk();
    }

    public function test_limited_chapter_requires_an_invitation(): void
    {
        $limited = $this->chapter('Invited Only', 6, 'limited');

        $this->actingAs($this->reader)
            ->get(route('books.read', [$this->book, $limited]))
            ->assertStatus(403)
            ->assertDontSee('Secret body of Invited Only');

        AccessGrant::create([
            'user_id' => $this->reader->id,
            'book_id' => $this->book->id,
            'granted_at' => now(),
        ]);

        $this->actingAs($this->reader)
            ->get(route('books.read', [$this->book, $limited]))
            ->assertOk();
    }

    public function test_paid_chapter_requires_a_verified_order(): void
    {
        $paid = $this->chapter('Paywalled', 7, 'paid');

        $this->actingAs($this->reader)
            ->get(route('books.read', [$this->book, $paid]))
            ->assertStatus(403)
            ->assertDontSee('Secret body of Paywalled');

        $order = $this->book->orders()->create([
            'user_id' => $this->reader->id,
            'reference' => 'PNT-TEST1234',
            'status' => 'pending',
            'amount' => 50000,
            'currency' => 'IDR',
        ]);

        $this->actingAs($this->reader)
            ->get(route('books.read', [$this->book, $paid]))
            ->assertStatus(403);

        $order->update(['status' => 'paid', 'approved_at' => now()]);

        $this->actingAs($this->reader)
            ->get(route('books.read', [$this->book, $paid]))
            ->assertOk()
            ->assertSee('Secret body of Paywalled');
    }

    public function test_another_author_cannot_edit_the_book(): void
    {
        $this->actingAs($this->reader)
            ->get(route('dashboard.books.edit', $this->book))
            ->assertForbidden();
    }
}
