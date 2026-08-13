<?php
// MEMBER HOME PAGE: protected landing page containing the main JavaScript interactions.
declare(strict_types=1);
require __DIR__ . '/member_auth.php';
requireMember();
require __DIR__ . '/db.php';
require __DIR__ . '/member_nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Moonlight Library: books, study spaces, events, and community learning.">
  <link rel="stylesheet" href="csslab.css">
  <script src="script.js" defer></script>
  <title>Moonlight Library | Home</title>
</head>
<body>
  <header id="top">
    <h1>🌙 Moonlight Library</h1>
    <p><em>Where every page lights a new path.</em></p>
    <nav aria-label="Main navigation">
      <a href="index.php" aria-current="page">Home</a>
      <a href="catalogue.php">Book Catalogue</a>
      <a href="events.php">Events</a>
      <a href="gallery.php">Gallery</a>
      <a href="contact.php">Contact Us</a>
      <a href="member_logout.php">Log Out</a>
      <?= memberProfileNavigation($mysqli) ?>
    </nav>
    <hr>
  </header>

  <main>
    <!-- JAVASCRIPT INTERACTION: personalized welcome stored locally in the browser. -->
    <section class="welcome-panel full-width" aria-live="polite">
      <h2 id="welcome-message">Welcome to Moonlight Library</h2>
      <p id="welcome-note">We are glad you stopped by.</p>
      <form id="welcome-form" class="welcome-form">
        <label for="visitor-name">Your name</label>
        <input type="text" id="visitor-name" name="visitor_name" maxlength="40" autocomplete="name" placeholder="Enter your name">
        <button type="submit">Personalise welcome</button>
        <button type="button" id="clear-name" class="secondary-button">Forget my name</button>
      </form>
    </section>

    <!-- JAVASCRIPT INTERACTION: random recommendations and a show/hide library fact. -->
    <section>
      <h2>Welcome, curious reader!</h2>
      <p>Moonlight Library is a community library for readers, researchers, dreamers, and lifelong learners. Explore our shelves, attend a reading circle, or find a quiet corner to study.</p>
      <p><a href="member_profile.php"><strong>View your member profile →</strong></a></p>
    </section>

    <section>
      <h2>Discover something new</h2>
      <p id="book-recommendation">Select the button for a reading recommendation from our shelves.</p>
      <button type="button" id="recommend-book">Recommend a book</button>
      <button type="button" id="toggle-fact" aria-expanded="false" aria-controls="library-fact">Show library fact</button>
      <p id="library-fact" class="is-hidden">Libraries do more than lend books: they provide shared spaces for learning, research, creativity, and community connection.</p>
    </section>

    <section>
      <h2>What you can discover</h2>
      <ul>
        <li>More than <strong>12,000 books</strong> across many subjects</li>
        <li>Quiet reading and group-study areas</li>
        <li>Free community events and workshops</li>
        <li>Newspapers, magazines, and reference materials</li>
      </ul>
    </section>

    <section class="full-width">
      <h2>This week's spotlight</h2>
      <article>
        <h3>📖 The African Writers Shelf</h3>
        <p>Travel across the continent through novels, poetry, biographies, and folktales. This week's featured theme is <q>Stories that remember where we came from.</q></p>
        <p><a href="catalogue.php#featured">See featured books</a></p>
      </article>
      <article>
        <h3>🎤 Saturday Story Circle</h3>
        <p><time datetime="2026-08-01T14:00">Saturday, 1 August 2026 at 2:00 p.m.</time></p>
        <p>Bring a short story, poem, or simply your listening ears. <a href="events.php">View event details</a>.</p>
      </article>
    </section>

    <aside>
      <h2>Library hours</h2>
      <table>
        <caption>Opening hours this week</caption>
        <thead><tr><th scope="col">Day</th><th scope="col">Hours</th></tr></thead>
        <tbody>
          <tr><td>Monday–Friday</td><td><time datetime="08:00">8:00 a.m.</time>–<time datetime="20:00">8:00 p.m.</time></td></tr>
          <tr><td>Saturday</td><td><time datetime="09:00">9:00 a.m.</time>–<time datetime="17:00">5:00 p.m.</time></td></tr>
          <tr><td>Sunday</td><td>Closed</td></tr>
        </tbody>
      </table>
    </aside>
  </main>

  <footer>
    <hr>
    <p><small>&copy; 2026 Moonlight Library. Knowledge belongs to everyone.</small></p>
    <p><a href="#top">Back to top ↑</a></p>
  </footer>
</body>
</html>
