<?php
/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 * @author François Gannaz <francois.gannaz@silecs.info>
 */

require dirname(__DIR__) . '/HtmlToTex.php';

class HtmlToTexTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideSimpleFragments
     */
    public function testSimpleFragments($html, $tex)
    {
        $conv = new HtmlToTex();
        $conv->loadFragment($html);
        $this->assertEquals($tex, $conv->toTex());
    }

    public function provideSimpleFragments()
    {
        return [
            [
                '<p>ζ</p>',
                "\n\nζ\n\n"
            ],
            [
                'Bonjour, $f^2(x)$',
                'Bonjour, \$f\^{}2(x)\$',
            ],
            [
                "a\n\nb",
                'a  b',
            ],
            [
                '<p>Bonjour, <b>moi</b> !</p><div class="xx"><i class="none">Pas $toi$</i>, ^moi_même &amp; et c\'est tout&nbsp;!</div>',
                '

Bonjour, \textbf{moi} !



\textit{Pas \$toi\$}, \^{}moi\_même \& et c\'est tout !

'
            ],
            [
                "<ul><li>a</li>\n<li>b</li></ul>",
                '\begin{itemize}\item[]a \item[]b\end{itemize}',
            ],
            [
                "<table><thead><tr><th>A</th><th>B</th></tr></thead>\n<tbody><tr><td>a</td><td>b</td></tr></tbody></table>",
                '\begin{tabular}{|c|c|}\hline {\bf{}A} & {\bf{}B} \\\\ \hline a & b \\\\ \hline\end{tabular}',
            ],
            [
                '<img src="http://home.gna.org/auto-qcm/logo.png" />',
                '\includegraphics[scale=.75]{/tmp/492e9f9f431214f9847bc46d916768e3}'
            ],
            [
                '<code class="tex">$$ \phi^2 \sim f_2 $$</code>',
                '$$ \phi^2 \sim f_2 $$'
            ],
            [
                '<code>$$ \phi^2 \sim f_2 $$</code>',
                '\begin{verbatim}\$\$ \textbackslash{}phi\^{}2 \textbackslash{}sim f\_2 \$\$\end{verbatim}'
            ],
            [
                "<table><thead><tr><th>A</th><th>B</th></tr></thead>\n<tbody><tr><td>a</td><td><table><tr><td>x</td><td>y</td></tr></table></td></tr></tbody></table>",
                '\begin{tabular}{|c|c|}\hline {\bf{}A} & {\bf{}B} \\\\ \hline a & \begin{tabular}{|c|c|}\hline x & y \\\\ \hline\end{tabular} \\\\ \hline\end{tabular}',
            ],
            [
                "<strong>L1<br />L2<p></p>L3</strong>",
                '{\bf{}L1\newline{}L2



L3}',
            ],
        ];
    }
}