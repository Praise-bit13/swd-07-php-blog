<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$pdo = db();
$adminId = (int) $pdo->query('SELECT id FROM admins ORDER BY id LIMIT 1')->fetchColumn();
if ($adminId === 0) {
    fwrite(STDERR, "No admin account exists. Create one with create-admin.php before seeding posts.\n");
    exit(1);
}

$seedCategories = [
    'tutorials' => 'Tutorials',
    'news' => 'News',
    'career' => 'Career',
];
$categoryIds = [];
$pdo->beginTransaction();

try {
    foreach ($seedCategories as $slug => $name) {
        $category = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
        $category->execute(['slug' => $slug]);
        $categoryId = $category->fetchColumn();

        if ($categoryId === false) {
            $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (:name, :slug)');
            $insertCategory->execute([
                'name' => $name,
                'slug' => unique_slug($pdo, 'categories', $name),
            ]);
            $categoryId = $pdo->lastInsertId();
        }
        $categoryIds[$slug] = (int) $categoryId;
    }

    $stories = [
        [
            'tutorials',
            'Build a Better Morning Routine That Actually Sticks',
            'A practical, low-pressure guide to designing a morning that gives your day a calmer start.',
            "A good morning routine is not a long checklist or a 5 a.m. alarm. It is a small sequence of choices that makes the first hour of your day feel more intentional.\n\nStart by choosing one anchor habit. It might be opening the curtains, drinking a glass of water, or taking five minutes to write down what matters today. Keep it small enough to repeat on a busy morning.\n\nOnce the anchor feels natural, add another step only if it genuinely helps. Put distractions out of reach, prepare what you can the night before, and leave room for life to be unpredictable. Consistency comes from making the routine easy to return to, not from making it perfect.",
        ],
        [
            'tutorials',
            'A Beginner-Friendly Guide to Writing Clearer Emails',
            'Make everyday messages easier to read with a clear subject, a direct opening, and one useful next step.',
            "A useful email respects the reader's time. Before you write, decide what the recipient needs to know or do. That answer becomes the centre of your message.\n\nChoose a subject line that describes the topic and, when helpful, the action. Put the key point near the top, use short paragraphs, and separate multiple questions into a short list. Replace vague phrases such as “just checking in” with the specific context the reader needs.\n\nBefore sending, read the message once from the recipient's perspective. Is the deadline clear? Is the request easy to find? A few small edits can make your email both warmer and more efficient.",
        ],
        [
            'tutorials',
            'Plan a Personal Budget in One Quiet Afternoon',
            'A straightforward framework for understanding monthly costs, setting priorities, and building a little breathing room.',
            "A budget is a map, not a judgment. Begin with the numbers you can verify: take-home income, recurring bills, debt payments, and recent everyday spending.\n\nGroup expenses into essentials, commitments, and flexible choices. A few months of bank statements can reveal patterns that are easy to miss in the moment. Set aside a realistic amount for savings and irregular costs, even if you start small.\n\nReview your plan once a month and adjust it when circumstances change. The most useful budget is one you understand and can keep using, not one built around someone else's rules.",
        ],
        [
            'tutorials',
            'Turn a Big Project Into a Week of Manageable Steps',
            'Break intimidating work into visible milestones and daily actions without losing sight of the bigger goal.',
            "Large projects often feel difficult because the next step is unclear. Write down what “finished” means, then divide that result into a handful of milestones you can recognize.\n\nFor each milestone, choose a concrete action that fits into one focused work session. “Work on the presentation” is hard to start; “draft the opening slide” gives you a place to begin. Add a little space between milestones for feedback and unexpected work.\n\nAt the end of each session, note what you accomplished and what comes next. That small handoff helps you restart without spending your next work block reconstructing your plan.",
        ],
        [
            'news',
            'Why Small Community Projects Have a Lasting Impact',
            'Local initiatives build stronger connections by inviting people to solve practical problems together.',
            "A community project can begin with a modest question: what would make this shared space, service, or neighbourhood easier to use? The strongest ideas often come from listening to the people who will experience the change.\n\nSmall projects make participation accessible. A weekend clean-up, a shared tool shelf, or a regular skills exchange gives neighbours a concrete way to contribute without waiting for a large organization to act.\n\nThe outcome is not only the finished project. People learn who has useful experience, where resources are available, and how to work together next time. Those connections can outlast the first project and make future efforts more resilient.",
        ],
        [
            'news',
            'A New Wave of Independent Creators Is Sharing What They Learn',
            'More creators are opening up their process and giving audiences a useful look behind the finished work.',
            "The polished result is no longer the only part of creative work audiences want to see. Independent makers are sharing drafts, decisions, and lessons learned along the way.\n\nThat transparency can make specialist skills feel more approachable. A detailed breakdown of how a designer chose a layout or how a writer revised a first draft offers practical insight that a finished portfolio cannot show by itself.\n\nSharing process also creates room for thoughtful conversation. When creators explain what they tried and why, readers can learn from the reasoning, even when their own projects take a different direction.",
        ],
        [
            'news',
            'Public Libraries Are Reimagining What a Shared Space Can Be',
            'Modern library programmes bring together access to information, useful services, and welcoming places to learn.',
            "Libraries have always helped people find information. Many are now extending that role through workshops, community events, technology access, and practical support.\n\nThe changes are shaped by local needs. One branch may host language exchanges, while another offers digital-skills sessions or a quiet place for students to work. The common thread is access: people can explore a new interest without first buying equipment or joining a private service.\n\nAs community needs change, libraries remain a trusted place to ask questions and discover what is available nearby. Their evolving programmes show how a familiar institution can keep serving new generations.",
        ],
        [
            'news',
            'The Case for Making More Time to Read Slowly',
            'Readers are rediscovering the value of a few uninterrupted pages in a day crowded with updates and alerts.',
            "Reading slowly is less about finishing a certain number of books and more about giving an idea enough time to settle. A few pages without switching tasks can feel surprisingly different from skimming between notifications.\n\nSome readers set aside a short daily window, keep a book where they already take a break, or choose one longer article each week. The routine works best when it fits naturally into an existing day.\n\nThe benefit is not measured only in pages. Staying with one piece of writing can make it easier to follow a complex argument, notice a detail, or simply enjoy the rhythm of a story.",
        ],
        [
            'career',
            'How to Prepare for a Thoughtful Career Conversation',
            'A little reflection helps you ask clearer questions and get more value from a conversation about your next step.',
            "A career conversation works best when you arrive with a sense of what you want to understand. You do not need a complete plan. One or two honest questions are enough to begin.\n\nConsider what gives you energy, what skills you want to practise, and which constraints matter in your current situation. Bring a recent example if you are asking for feedback on a specific challenge. Concrete context makes the conversation more useful for both people.\n\nEnd by noting one action you can take, even if it is simply learning more about an option. A thoughtful conversation is not a final answer; it is a chance to see the next step more clearly.",
        ],
        [
            'career',
            'The Skills That Make Remote Collaboration Feel Human',
            'Good remote teamwork depends on clear agreements, generous context, and deliberate moments of connection.',
            "When teammates are not in the same room, small assumptions can turn into delays. Clear written updates help people understand what changed, who owns the next action, and when a decision is needed.\n\nShare context along with a request. Explain the goal, link to the relevant material, and distinguish between a decision that is urgent and one that can wait. This makes it easier for colleagues in different schedules to contribute thoughtfully.\n\nRemote work still needs human connection. Regular check-ins, appreciation for good work, and space to raise concerns help a team feel like a group of people rather than a list of tasks.",
        ],
        [
            'career',
            'A Practical Way to Keep Learning Alongside a Busy Job',
            'Build professional development into small, repeatable sessions instead of waiting for a perfectly quiet week.',
            "Learning at work can feel like one more responsibility. It becomes easier to sustain when you define a specific skill and connect it to work you already do.\n\nChoose a short resource, schedule a recurring session, and use one idea in a real task as soon as possible. Applying what you learn helps you see what is useful and what needs more practice. Keep a simple note of questions that come up as you go.\n\nProgress is easier to notice when you look back occasionally. A list of small experiments, useful feedback, and new responsibilities can show how far your skills have developed over a few months.",
        ],
        [
            'career',
            'Write a Portfolio Case Study That Shows How You Think',
            'Help readers understand the problem, your decisions, and the outcome—not only the finished work.',
            "A strong case study makes your contribution understandable. Begin with the problem and the people affected by it. Share enough context for a reader to understand why the project mattered without overwhelming them with background.\n\nDescribe your role, the options you considered, and the reasons behind important decisions. Be precise about collaborators and constraints. A clear account of trade-offs can be more informative than a claim that everything went smoothly.\n\nFinish with the outcome and what you learned. If you have evidence, explain what it measures and where it came from. Honest detail helps a portfolio feel credible and gives future teammates a better sense of how you approach work.",
        ],
    ];

    $titleCheck = $pdo->prepare('SELECT id FROM posts WHERE title = :title LIMIT 1');
    $insertPost = $pdo->prepare(
        'INSERT INTO posts
            (category_id, author_id, title, slug, excerpt, body, status, published_at)
         VALUES
            (:category_id, :author_id, :title, :slug, :excerpt, :body, :status, :published_at)'
    );
    $created = 0;
    $skipped = 0;

    foreach ($stories as $index => [$categorySlug, $title, $excerpt, $body]) {
        $titleCheck->execute(['title' => $title]);
        if ($titleCheck->fetch()) {
            $skipped++;
            continue;
        }

        $insertPost->execute([
            'category_id' => $categoryIds[$categorySlug],
            'author_id' => $adminId,
            'title' => $title,
            'slug' => unique_slug($pdo, 'posts', $title),
            'excerpt' => $excerpt,
            'body' => $body,
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s', time() - (($index + 1) * 86400)),
        ]);
        $created++;
    }

    $pdo->commit();
    fwrite(STDOUT, "Seed complete: {$created} post(s) created, {$skipped} already present.\n");
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Could not seed the blog: " . $exception->getMessage() . "\n");
    exit(1);
}
