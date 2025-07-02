<?php

namespace App\DataFixtures;

use App\Entity\ForumPost;
use App\Entity\News;
use App\Entity\NewsComment;
use App\Entity\NewsPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Forum posts and answers
        for ($i = 1; $i <= 250; $i++) {
            $post = new ForumPost();
            $post->setTitle("Sujet du forum $i");
            $post->setBody("Contenu principal du sujet $i.");
            $post->setAuthor("Auteur" . rand(1, 10));
            $post->setParentId(null);
            $post->setFlag(rand(0, 1) ? true : false);
            $manager->persist($post);
            $manager->flush();
            $numAnswers = rand(3, 25);
            for ($j = 1; $j <= $numAnswers; $j++) {
                $answer = new ForumPost();
                $answer->setTitle(null);
                $answer->setBody("Réponse $j au sujet $i.");
                $answer->setAuthor("Répondeur" . rand(1, 10));
                $answer->setParentId($post->getId());
                $post->setFlag(rand(0, 1) ? true : false);
                $manager->persist($answer);
            }

        }

        // News and comments
        for ($i = 1; $i <= 100; $i++) {
            $news = new NewsPost();
            $news->setTitle("Actualité $i");
            $news->setContent(
                "Contenu de l'actualité $i. " .
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit. " .
                "Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. " .
                "Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. " .
                "Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. " .
                "Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim. Pellentesque congue. " .
                "Ut in risus volutpat libero pharetra tempor. Cras vestibulum bibendum augue. Praesent egestas leo in pede. " .
                "Praesent blandit odio eu enim. Pellentesque sed dui ut augue blandit sodales. " .
                "Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam nibh. " .
                "Mauris ac mauris sed pede pellentesque fermentum. Maecenas adipiscing ante non diam sodales hendrerit. " .
                "Ut velit mauris, egestas sed, gravida nec, ornare ut, mi. Aenean ut orci vel massa suscipit pulvinar. " .
                "Nulla sollicitudin. Fusce varius, ligula non tempus aliquam, nunc turpis ullamcorper nibh, in tempus sapien eros vitae ligula. " .
                "Pellentesque rhoncus nunc et augue. Integer id felis. Curabitur aliquet pellentesque diam. Integer quis metus vitae elit lobortis egestas. " .
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit. " .
                "Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. " .
                "Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. " .
                "Proin porttitor, orci nec nonummy molestie, enim est eleifend mi, non fermentum diam nisl sit amet erat. " .
                "Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim. Pellentesque congue. " .
                "Ut in risus volutpat libero pharetra tempor. Cras vestibulum bibendum augue. Praesent egestas leo in pede. " .
                "Praesent blandit odio eu enim. Pellentesque sed dui ut augue blandit sodales. " .
                "Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam nibh. " .
                "Mauris ac mauris sed pede pellentesque fermentum. Maecenas adipiscing ante non diam sodales hendrerit. " .
                "Ut velit mauris, egestas sed, gravida nec, ornare ut, mi. Aenean ut orci vel massa suscipit pulvinar. " .
                "Nulla sollicitudin. Fusce varius, ligula non tempus aliquam, nunc turpis ullamcorper nibh, in tempus sapien eros vitae ligula. " .
                "Pellentesque rhoncus nunc et augue. Integer id felis. Curabitur aliquet pellentesque diam. Integer quis metus vitae elit lobortis egestas."
            );
            $news->setAuthor("Rédacteur" . rand(1, 10));
            $news->setImgPath("https://picsum.photos/id/$i/1280/774.jpg");
            $manager->persist($news);
            $manager->flush();

            $numComments = rand(0, 5);
            for ($j = 1; $j <= $numComments; $j++) {
                $comment = new NewsComment();
                $comment->setContent("Commentaire $j sur l'actualité $i.");
                $comment->setAuthor("Commentateur" . rand(1, 10));
                $comment->setNewsId($news);
                $manager->persist($comment);
            }
        }

        $manager->flush();
    }
}
