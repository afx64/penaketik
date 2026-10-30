<?php

namespace Database\Seeders;

use App\Models\AccessGrant;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\StoreLink;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::firstOrCreate(
            ['email' => 'author@penaketik.test'],
            [
                'name' => 'Nadia Prameswari',
                'pen_name' => 'Nadia Prameswari',
                'role' => 'author',
                'password' => Hash::make('password'),
                'bio' => 'Novelist and short-story writer from Bandung. Writes about the sea, memory, and the people who keep both.',
                'website' => 'https://example.com',
            ]
        );

        $reader = User::firstOrCreate(
            ['email' => 'reader@penaketik.test'],
            [
                'name' => 'Rafi Adhitama',
                'role' => 'reader',
                'password' => Hash::make('password'),
            ]
        );

        // Keep roles correct when re-seeding an existing database.
        $author->forceFill(['role' => 'author'])->save();
        $reader->forceFill(['role' => 'reader'])->save();

        $book = Book::updateOrCreate(
            ['slug' => 'laut-yang-menyimpan-nama'],
            [
                'user_id' => $author->id,
                'title' => 'Laut yang Menyimpan Nama',
                'subtitle' => 'A novel about the things the tide brings back',
                'genre' => 'Literary fiction',
                'language' => 'id',
                'synopsis' => "Sari returns to the fishing town she left at seventeen to bury a father she barely spoke to. In his boat shed she finds a ledger of names — neighbours, strangers, a child who disappeared the same summer she left.\n\nMoving between 1998 and the present, the novel follows three women who keep the town's accounts, and the one name every ledger refuses to hold.",
                'status' => 'published',
                'is_featured' => true,
                'price' => 45000,
                'currency' => 'IDR',
                'published_at' => now()->subDays(20),
                'meta_description' => 'Sari returns to the fishing town she left at seventeen and finds a ledger of names, a missing child, and the truth about her father.',
                'meta_keywords' => 'indonesian novel, literary fiction, coastal, family',
            ]
        );

        $freeBook = Book::updateOrCreate(
            ['slug' => 'the-cartographers-apprentice'],
            [
                'user_id' => $author->id,
                'title' => "The Cartographer's Apprentice",
                'subtitle' => 'A serial in three parts',
                'genre' => 'Fantasy',
                'language' => 'en',
                'synopsis' => 'Every map in the empire is drawn from memory, and memory can be edited. Apprentice mapmaker Ilya discovers her teacher has been erasing villages — and that her own hometown is next.',
                'status' => 'published',
                'is_featured' => false,
                'price' => 0,
                'currency' => 'IDR',
                'published_at' => now()->subDays(5),
                'meta_description' => 'A free fantasy serial about maps drawn from memory, and the villages erased from them.',
                'meta_keywords' => 'fantasy serial, free novel, maps',
            ]
        );

        $chapters = [
            [
                'title' => 'The Tide Ledger',
                'access' => 'public',
                'position' => 1,
                'content' => '<p>The boat shed smelled of salt and diesel and something older, like paper left out in the rain. Sari stood in the doorway with her suitcase still in her hand and let her eyes adjust.</p>'
                    .'<p>Her father had kept his accounts in pencil. Every page of the ledger was ruled in the same careful hand, the numbers lined up like small boats at a jetty. But the last thirty pages were not numbers. They were names.</p>'
                    .'<blockquote>“The sea does not take,” he had told her once, on a night she remembered mostly as heat and the sound of frogs. “It only keeps what we leave behind.”</blockquote>'
                    .'<p>She turned the page. Her mother\'s name was there. Hers was not.</p>',
            ],
            [
                'title' => 'What the Neighbours Kept',
                'access' => 'public',
                'position' => 2,
                'content' => '<p>The town had not grown so much as rearranged itself. The mosque was still there. The market had moved inland, away from the water, and the warung where she used to buy ice cream had become a mobile phone shop.</p>'
                    .'<p>Bu Yanti recognised her before Sari had finished saying her name.</p>'
                    .'<p>“You have his walk,” she said, and then, because she was old enough to say such things, “and his stubborn mouth.”</p>',
            ],
            [
                'title' => 'Nineteen Ninety-Eight',
                'access' => 'public',
                'position' => 3,
                'content' => '<p>In May 1998 the town burned for two days. Not the houses — the records. The district office, the school, the church registry. People said it was the rioting; people said it was an accident; people said nothing at all and rebuilt the shelves with new paper.</p>'
                    .'<p>Sari was seventeen. She remembered the smoke and the way her father stood in the road, not stopping anyone, not helping, just counting.</p>',
            ],
            [
                'title' => 'The Name That Would Not Stay',
                'access' => 'limited',
                'position' => 4,
                'content' => '<p>There is a page in the ledger where the pencil has been erased so many times the paper has gone soft as cloth. Sari held it up to the lamp. The ghost of a name surfaced, then sank.</p>'
                    .'<p>She would need better light. She would need someone who had been there.</p>',
            ],
            [
                'title' => 'Low Water',
                'access' => 'paid',
                'position' => 5,
                'content' => '<p>At low tide you can walk to the sandbar, and from the sandbar you can see the second boat shed, the one that does not appear on the harbour map.</p>'
                    .'<p>Her father had drawn that map himself. Sari had always assumed he had simply forgotten.</p>',
            ],
        ];

        foreach ($chapters as $index => $data) {
            Chapter::updateOrCreate(
                ['book_id' => $book->id, 'slug' => \Illuminate\Support\Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'access' => $data['access'],
                    'position' => $data['position'],
                    'content' => $data['content'],
                    'is_published' => true,
                ]
            );
        }

        $freeChapters = [
            ['title' => 'Ink That Remembers', 'access' => 'public', 'position' => 1, 'content' => '<p>The first rule of mapmaking, Ilya was told, is that a map is not a picture of a place. It is a promise about what a place will let you do.</p><p>She was eleven, and she believed it the way she believed the sea was full of fish: completely, and without ever checking.</p>'],
            ['title' => 'The Villages That Went Missing', 'access' => 'free', 'position' => 2, 'content' => '<p>Master Ordan kept the empire\'s maps in a room with no windows and a door that locked from the inside. Ilya was not allowed in. This had never stopped her.</p><p>On the third shelf, behind the coastal survey, she found a plain grey codex with no title. Every page was blank except for the faint impressions of names.</p>'],
            ['title' => 'A Road That Ends', 'access' => 'free', 'position' => 3, 'content' => '<p>“If a place is not on the map,” Ordan said, without turning around, “then no one can be blamed for what happens there.”</p>'],
        ];

        foreach ($freeChapters as $data) {
            Chapter::updateOrCreate(
                ['book_id' => $freeBook->id, 'slug' => \Illuminate\Support\Str::slug($data['title'])],
                $data + ['is_published' => true]
            );
        }

        AccessGrant::firstOrCreate(
            ['user_id' => $reader->id, 'book_id' => $book->id],
            ['note' => 'Early reader', 'granted_at' => now()]
        );

        \App\Models\BookLike::firstOrCreate([
            'user_id' => $reader->id,
            'book_id' => $freeBook->id,
        ]);

        \App\Models\Comment::firstOrCreate(
            ['user_id' => $reader->id, 'book_id' => $freeBook->id, 'body' => 'The mapmaking rules alone sold me. Waiting on the next part.']
        );

        StoreLink::firstOrCreate(
            ['user_id' => $author->id, 'platform' => 'Google Play Books', 'url' => 'https://play.google.com/store/books'],
            ['label' => 'eBook edition', 'position' => 1]
        );

        StoreLink::firstOrCreate(
            ['user_id' => $author->id, 'platform' => 'Kobo', 'url' => 'https://www.kobo.com'],
            ['label' => 'Kobo edition', 'position' => 2]
        );

        Settings::put([
            'site_tagline' => 'Stories, serialised at your own pace.',
            'currency' => 'IDR',
            'bank_name' => 'BCA',
            'bank_account_name' => 'Nadia Prameswari',
            'bank_account_number' => '1234567890',
            'seo_site_title' => 'PenakeTik — an author-owned bookshop',
            'seo_site_description' => 'Read free previews, get invited to limited-free chapters, and buy full books straight from the author.',
            'seo_site_keywords' => 'indonesian fiction, serial novel, indie author, bookstore',
            'mail_from_address' => 'hello@penaketik.test',
            'mail_from_name' => 'PenakeTik',
        ]);

        $this->command?->info('Demo accounts:');
        $this->command?->info('  author@penaketik.test / password (author studio)');
        $this->command?->info('  reader@penaketik.test / password (reader account)');
    }
}
