# gdd2010-pacman

[Google Developers Day 2010](http://web.archive.org/web/20100724051456/http://www.google.co.jp/events/developerday/2010/tokyo)の参加資格を得るためのDevQuizで[Pacman問題](http://koriym.github.io/gdd2010-pacman/)が出題されました。

使用言語は不問で問題は３問あり手動でトライすることもできました。

問題1
```
###########
#.V..#..H.#
#.##...##.#
#L#..#..R.#
#.#.###.#.#
#....@....#
###########
```
http://jsrun.it/asannou/pacman

問題2
```
####################
###.....L..........#
###.##.##.##L##.##.#
###.##.##.##.##.##.#
#.L................#
#.##.##.##.##.##.###
#.##.##L##.##.##.###
#.................L#
#.#.#.#J####J#.#.#.#
#L.................#
###.##.##.##.##.##.#
###.##.##R##.##.##.#
#................R.#
#.##.##.##.##R##.###
#.##.##.##.##.##.###
#@....R..........###
####################
```
http://jsrun.it/asannou/pacman2


 
問題3
```
##########################################################
#........................................................#
#.###.#########.###############.########.###.#####.#####.#
#.###.#########.###############.########.###.#####.#####.#
#.....#########....J.............J.......###.............#
#####.###.......#######.#######.########.###.#######.#####
#####.###.#####J#######.#######.########.###.##   ##.#####
#####.###L#####.##   ##L##   ##.##    ##.###.##   ##.#####
#####.###..H###.##   ##.##   ##.########.###.#######J#####
#####.#########.##   ##L##   ##.########.###.###V....#####
#####.#########.#######.#######..........###.#######.#####
#####.#########.#######.#######.########.###.#######.#####
#.....................L.........########..........R......#
#L####.##########.##.##########....##....#########.#####.#
#.####.##########.##.##########.##.##.##.#########.#####.#
#.................##............##..@.##...............R.#
##########################################################
```
http://jsrun.it/asannou/pacman3

PHPで問題を解きました。以下は問題3の解答です。

```
lkkllljjllhhkkkllllllllllllkkkhhhhllllkkkkkhhkkklllllljjjhhhhjjjjjjjjlllljjjhhhhhhhhhhhhhhhhkkkllllkkkkkkkjjjjjjjllllllllkkkkkkkkllllkkkhhhhhhhhhhhhhhhhjjjhhhhhhhhhkkkhhhhhhhhhhhhhhhhjjjlllllllljjjjjjjjllllllllkkkkkkkkhhhhhhhhjjjjjjjjhhhjjjlllllllllllkkllljjlllkklllkkkkkkkkkhhhhhhhhhkkkllllllllllllljjjlllllllljjjjjjjjhhjjjhhhhhhhhhhkkhhhjjhhhkkhhhkkklllllllllkkkkkkkkkhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhjjjlllljjjjjjjjllllllllllkkkkkkkkkkkhhhhhhhhhhjjjhhhhkkklllljjjjjjjjjjjhhhhjjjlllllkkkhhhhhjjjllllllllllllllllkkkhhkkkkkkkklllllllljjjjjjjjhhhhhhjjjhhhhhhhhhhhkkkllllllllllljjjhhhhhhhhhhhkkkhhhhhjjjlllllkkklllllllllkkkkkkkhhhhhhjjjl
```
[ゲームプレイのアニメーション](http://jsrun.it/asannou/pacman3?move=lkkllljjllhhkkkllllllllllllkkkhhhhllllkkkkkhhkkklllllljjjhhhhjjjjjjjjlllljjjhhhhhhhhhhhhhhhhkkkllllkkkkkkkjjjjjjjllllllllkkkkkkkkllllkkkhhhhhhhhhhhhhhhhjjjhhhhhhhhhkkkhhhhhhhhhhhhhhhhjjjlllllllljjjjjjjjllllllllkkkkkkkkhhhhhhhhjjjjjjjjhhhjjjlllllllllllkkllljjlllkklllkkkkkkkkkhhhhhhhhhkkkllllllllllllljjjlllllllljjjjjjjjhhjjjhhhhhhhhhhkkhhhjjhhhkkhhhkkklllllllllkkkkkkkkkhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhjjjlllljjjjjjjjllllllllllkkkkkkkkkkkhhhhhhhhhhjjjhhhhkkklllljjjjjjjjjjjhhhhjjjlllllkkkhhhhhjjjllllllllllllllllkkkhhkkkkkkkklllllllljjjjjjjjhhhhhhjjjhhhhhhhhhhhkkkllllllllllljjjhhhhhhhhhhhkkkhhhhhjjjlllllkkklllllllllkkkkkkkhhhhhhjjjl
)で確認することができます。

## 実行方法

```
git clone https://github.com/koriym/gdd2010-pacman.git
cd gdd2010-pacman
php pacman.php
```

## リファレンス

 * [エンジニアを熱狂させたグーグル「DevQuiz」は、日本生まれ世界育ち](http://www.atmarkit.co.jp/ait/articles/1202/14/news139.html)
 * [BEAR Blog - GDD 2010 Pacman問題](http://koriym.github.io/2010/09/gdd-2010-pacman%E5%95%8F%E9%A1%8C/)
 
